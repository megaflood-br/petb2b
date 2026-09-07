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
use SplFileObject;
use Throwable;

class WordpressImportController extends Controller
{
    private const BATCH_SIZE = 20;

    private const BATCH_SIZE_WITH_IMAGES = 1;

    private const IMAGES_PER_BATCH = 2;

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
        $path = "wxr/{$token}.jsonl";

        try {
            $this->writeJsonl($path, $extracted['posts']);
        } catch (Throwable $e) {
            report($e);

            return $this->fail($request, 'Não foi possível preparar os posts para importação. Tente um XML menor.');
        }

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
            'site_base_url' => $extracted['site_base_url'],
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
            'skip' => 'sometimes|boolean',
        ]);

        @set_time_limit(20);
        ini_set('memory_limit', '256M');

        if ($request->hasSession()) {
            $request->session()->save();
        }

        $key = $this->cacheKey($validated['token']);
        $state = Cache::get($key);

        if (! is_array($state) || ($state['user_id'] ?? null) !== $request->user()->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Importação expirada. Envie o XML novamente.',
            ], 422);
        }

        $total = (int) $state['total'];
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

        if ($request->boolean('skip') && $cursor < $total) {
            $stuck = $this->readPosts($state['file'], $cursor, 1)[0] ?? [];
            $title = (string) ($stuck['title'] ?? 'Post');
            $result->addError("“{$title}”: pulado (demorou demais).");
            $cursor++;

            return $this->persistProgress($key, $state, $result, $cursor, $total, $newSlugs, [], $validated['token']);
        }

        $importer
            ->setSiteBaseUrl((string) ($state['site_base_url'] ?? ''))
            ->hydrateDownloads($this->loadDownloads($validated['token']));

        if ($state['download_images']) {
            $importer->setImageBudget(self::IMAGES_PER_BATCH);
        }

        $batchSize = $state['download_images'] ? self::BATCH_SIZE_WITH_IMAGES : self::BATCH_SIZE;
        $posts = $this->readPosts($state['file'], $cursor, $batchSize);

        foreach ($posts as $post) {
            $slug = (string) ($post['slug'] ?? '');

            try {
                $outcome = $importer->importPayload($post, $result, (bool) $state['download_images']);
            } catch (Throwable $e) {
                $result->addError('“' . ($post['title'] ?? 'Post') . '”: ' . $e->getMessage());
                $cursor++;
                continue;
            }

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
        }

        return $this->persistProgress(
            $key,
            $state,
            $result,
            $cursor,
            $total,
            $newSlugs,
            $importer->downloadCache(),
            $validated['token']
        );
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  list<string>  $newSlugs
     * @param  array<string, string|null>  $downloads
     */
    private function persistProgress(
        string $key,
        array $state,
        WordpressImportResult $result,
        int $cursor,
        int $total,
        array $newSlugs,
        array $downloads,
        ?string $token = null
    ): JsonResponse {
        $done = $cursor >= $total;

        if ($done) {
            Storage::disk('local')->delete($state['file']);
            if ($token) {
                Storage::disk('local')->delete("wxr/{$token}-dl.json");
            }
            Cache::forget($key);
        } else {
            if ($token && $downloads !== []) {
                $this->saveDownloads($token, $downloads);
            }

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

    /**
     * @param  list<array<string, mixed>>  $posts
     */
    private function writeJsonl(string $relative, array $posts): void
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory(dirname($relative));
        $absolute = $disk->path($relative);
        $directory = dirname($absolute);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Não foi possível criar a pasta temporária da importação.');
        }

        $stream = fopen($absolute, 'w');
        if ($stream === false) {
            throw new \RuntimeException('Não foi possível gravar os posts extraídos.');
        }

        try {
            foreach ($posts as $post) {
                $line = json_encode($post, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                if ($line === false) {
                    continue;
                }
                fwrite($stream, $line . "\n");
            }
        } finally {
            fclose($stream);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readPosts(string $relative, int $offset, int $limit): array
    {
        $absolute = Storage::disk('local')->path($relative);
        if (! is_file($absolute) || $limit < 1) {
            return [];
        }

        $file = new SplFileObject($absolute, 'r');
        $file->setFlags(SplFileObject::DROP_NEW_LINE);
        if ($offset > 0) {
            $file->seek($offset);
        }

        $posts = [];
        while (count($posts) < $limit && ! $file->eof()) {
            $line = trim((string) $file->current());
            $file->next();
            if ($line === '') {
                continue;
            }

            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $posts[] = $decoded;
            }
        }

        return $posts;
    }

    /**
     * @return array<string, string|null>
     */
    private function loadDownloads(string $token): array
    {
        $raw = Storage::disk('local')->get("wxr/{$token}-dl.json");
        $map = is_string($raw) ? json_decode($raw, true) : null;

        return is_array($map) ? $map : [];
    }

    /**
     * @param  array<string, string|null>  $map
     */
    private function saveDownloads(string $token, array $map): void
    {
        Storage::disk('local')->put(
            "wxr/{$token}-dl.json",
            (string) json_encode($map, JSON_UNESCAPED_UNICODE)
        );
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
