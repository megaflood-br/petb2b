<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Wordpress\WordpressImportResult;
use App\Services\Wordpress\WordpressXmlImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class WordpressImportController extends Controller
{
    private const BATCH_SIZE = 20;

    private const BATCH_SIZE_WITH_IMAGES = 1;

    private const IMAGES_PER_BATCH = 3;

    public function store(Request $request, WordpressXmlImporter $importer): JsonResponse|RedirectResponse
    {
        if (! $request->hasFile('wordpress_xml')) {
            $phpMax = ini_get('upload_max_filesize') ?: '2M';
            $message = "O arquivo não chegou ao servidor. O XML do WordPress costuma ser grande — o limite atual do PHP é {$phpMax}. Aumente upload_max_filesize e post_max_size para pelo menos 64M.";

            return $this->fail($request, $message);
        }

        $request->validate([
            'wordpress_xml' => 'required|file|max:102400',
            'download_images' => 'nullable',
        ], [
            'wordpress_xml.required' => 'Envie o arquivo XML exportado do WordPress.',
            'wordpress_xml.file' => 'Envie o arquivo XML exportado do WordPress.',
            'wordpress_xml.max' => 'O XML pode ter no máximo 100 MB.',
        ]);

        $file = $request->file('wordpress_xml');
        $original = strtolower((string) $file->getClientOriginalName());
        if (! str_ends_with($original, '.xml')) {
            return $this->fail($request, 'O arquivo precisa ser um XML (.xml) exportado pelo WordPress.');
        }

        @set_time_limit(120);
        ini_set('memory_limit', '512M');

        $downloadImages = $request->boolean('download_images');

        if (! $request->wantsJson()) {
            return $this->importAllAtOnce($request, $importer, $file->getRealPath(), $downloadImages);
        }

        try {
            $extracted = $importer->extract($file->getRealPath());
        } catch (Throwable $e) {
            report($e);

            return $this->fail($request, 'Falha ao ler o XML: ' . $e->getMessage());
        }

        if ($extracted['posts'] === [] && $extracted['skipped'] === 0) {
            return $this->fail($request, 'Nenhum post publicado foi encontrado no XML. Exporte em Ferramentas → Exportar → Posts (não só páginas).');
        }

        if ($extracted['posts'] === []) {
            $result = new WordpressImportResult();
            $result->skipped = $extracted['skipped'];

            return response()->json([
                'ok' => true,
                'token' => null,
                'total' => 0,
                'skipped' => $extracted['skipped'],
                'done' => true,
                'summary' => $result->summary(),
            ]);
        }

        $token = (string) Str::uuid();
        $path = "wxr/{$token}.json";
        $encoded = json_encode(
            [
                'site_base_url' => $extracted['site_base_url'],
                'posts' => $extracted['posts'],
            ],
            JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($encoded === false) {
            return $this->fail($request, 'Não foi possível preparar os posts para importação. Tente um XML menor.');
        }

        Storage::disk('local')->put($path, $encoded);

        Cache::put($this->cacheKey($token), [
            'user_id' => $request->user()->id,
            'file' => $path,
            'cursor' => 0,
            'total' => count($extracted['posts']),
            'skipped' => $extracted['skipped'],
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'categories_created' => 0,
            'images_downloaded' => 0,
            'images_failed' => 0,
            'errors' => [],
            'download_images' => $downloadImages,
            'downloaded' => [],
            'new_slugs' => [],
        ], now()->addHours(2));

        return response()->json([
            'ok' => true,
            'token' => $token,
            'total' => count($extracted['posts']),
            'skipped' => $extracted['skipped'],
        ]);
    }

    public function process(Request $request, WordpressXmlImporter $importer): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|uuid',
        ]);

        @set_time_limit(50);
        ini_set('memory_limit', '512M');

        $key = $this->cacheKey($validated['token']);
        $state = Cache::get($key);

        if (! is_array($state) || ($state['user_id'] ?? null) !== $request->user()->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Importação expirada. Envie o XML novamente.',
            ], 422);
        }

        $raw = Storage::disk('local')->get($state['file']);
        $payload = is_string($raw) ? json_decode($raw, true) : null;
        $posts = is_array($payload['posts'] ?? null) ? $payload['posts'] : [];
        $total = count($posts);
        $cursor = (int) $state['cursor'];
        $newSlugs = $state['new_slugs'] ?? [];

        $result = new WordpressImportResult();
        $result->created = (int) $state['created'];
        $result->updated = (int) $state['updated'];
        $result->skipped = (int) $state['skipped'];
        $result->failed = (int) $state['failed'];
        $result->categoriesCreated = (int) $state['categories_created'];
        $result->imagesDownloaded = (int) $state['images_downloaded'];
        $result->imagesFailed = (int) $state['images_failed'];
        $result->errors = $state['errors'] ?? [];

        $importer
            ->setSiteBaseUrl((string) ($payload['site_base_url'] ?? ''))
            ->hydrateDownloads(is_array($state['downloaded'] ?? null) ? $state['downloaded'] : []);

        if ($state['download_images']) {
            $importer->setImageBudget(self::IMAGES_PER_BATCH);
        }

        $batchSize = $state['download_images'] ? self::BATCH_SIZE_WITH_IMAGES : self::BATCH_SIZE;
        $processedInBatch = 0;

        while ($cursor < $total && $processedInBatch < $batchSize) {
            $post = $posts[$cursor];
            $slug = (string) ($post['slug'] ?? '');
            $outcome = $importer->importPayload($post, $result, (bool) $state['download_images']);

            if ($outcome['inserted']) {
                $result->created++;
                $newSlugs[] = $slug;
            }

            if (! $outcome['done']) {
                break;
            }

            if (! $outcome['inserted'] && ! in_array($slug, $newSlugs, true)) {
                if ($outcome['changed']) {
                    $result->updated++;
                } else {
                    $result->skipped++;
                }
            }

            $cursor++;
            $processedInBatch++;
        }

        $done = $cursor >= $total;

        if ($done) {
            Storage::disk('local')->delete($state['file']);
            Cache::forget($key);
        } else {
            Cache::put($key, array_merge($state, [
                'cursor' => $cursor,
                'created' => $result->created,
                'updated' => $result->updated,
                'skipped' => $result->skipped,
                'failed' => $result->failed,
                'categories_created' => $result->categoriesCreated,
                'images_downloaded' => $result->imagesDownloaded,
                'images_failed' => $result->imagesFailed,
                'errors' => $result->errors,
                'downloaded' => $importer->downloadCache(),
                'new_slugs' => array_values(array_unique($newSlugs)),
            ]), now()->addHours(2));
        }

        return response()->json([
            'ok' => true,
            'done' => $done,
            'processed' => $cursor,
            'total' => $total,
            'percent' => $total === 0 ? 100 : (int) round(($cursor / $total) * 100),
            'created' => $result->created,
            'updated' => $result->updated,
            'skipped' => $result->skipped,
            'failed' => $result->failed,
            'summary' => $done ? $result->summary() : null,
            'errors' => $result->errors,
        ]);
    }

    private function importAllAtOnce(Request $request, WordpressXmlImporter $importer, string $path, bool $downloadImages): RedirectResponse
    {
        try {
            $result = $importer->import($path, $downloadImages);
        } catch (Throwable $e) {
            report($e);

            return back()->with('wordpress_import_error', 'Falha ao ler o XML: ' . $e->getMessage());
        }

        if ($result->created === 0 && $result->updated === 0 && $result->skipped === 0 && $result->failed === 0) {
            return back()->with(
                'wordpress_import_error',
                'Nenhum post publicado foi encontrado no XML. Exporte em Ferramentas → Exportar → Posts (não só páginas).'
            );
        }

        return back()
            ->with('message', $result->summary())
            ->with('wordpress_import_errors', $result->errors);
    }

    private function fail(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }

        return back()->with('wordpress_import_error', $message);
    }

    private function cacheKey(string $token): string
    {
        return 'wxr-import.' . $token;
    }
}
