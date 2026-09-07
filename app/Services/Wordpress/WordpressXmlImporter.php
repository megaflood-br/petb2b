<?php

namespace App\Services\Wordpress;

use App\Models\BlogCategory;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class WordpressXmlImporter
{
    private const FALLBACK_CATEGORY = ['name' => 'Geral', 'slug' => 'geral'];

    private WordpressImportResult $result;

    private string $siteBaseUrl = '';

    /** @var array<string, string|null> url canônica => caminho no disco public */
    private array $downloaded = [];

    private int $imageBudget = PHP_INT_MAX;

    private ?float $deadlineAt = null;

    /** @var list<string> */
    private array $uploadRoots = [];

    public function import(string $path, bool $downloadImages = true): WordpressImportResult
    {
        $extracted = $this->extract($path);
        if ($this->uploadRoots === []) {
            $this->setUploadRoots(self::resolveUploadRoots());
        }
        $this->result = new WordpressImportResult();
        $this->result->skipped = $extracted['skipped'];
        $this->siteBaseUrl = $extracted['site_base_url'];
        $this->downloaded = [];
        $this->imageBudget = PHP_INT_MAX;

        foreach ($extracted['posts'] as $payload) {
            $outcome = $this->importPayload($payload, $this->result, $downloadImages);
            if ($outcome['inserted']) {
                $this->result->created++;
            } elseif ($outcome['done'] && $outcome['changed']) {
                $this->result->updated++;
            } elseif ($outcome['done']) {
                $this->result->skipped++;
            }
        }

        return $this->result;
    }

    /**
     * Lê o WXR e devolve posts publicáveis (sem gravar no banco).
     *
     * @return array{posts: list<array<string, mixed>>, skipped: int, site_base_url: string}
     */
    public function extract(string $path): array
    {
        $xml = $this->loadXml($path);
        $channel = $this->findChannel($xml);

        $namespaces = $xml->getDocNamespaces(true) + $channel->getDocNamespaces(true);
        $wpNs = $this->namespaceUri($namespaces, 'wp', 'http://wordpress.org/export/1.2/');
        $contentNs = $this->namespaceUri($namespaces, 'content', 'http://purl.org/rss/1.0/modules/content/');
        $excerptNs = $this->namespaceUri($namespaces, 'excerpt', 'http://wordpress.org/export/1.2/excerpt/');

        $this->siteBaseUrl = $this->channelBaseUrl($channel, $wpNs);
        $attachments = $this->attachmentMap($channel, $wpNs);
        $posts = [];
        $skipped = 0;

        foreach ($this->items($channel) as $item) {
            $postType = $this->nsValue($item, $wpNs, 'post_type') ?: 'post';
            if ($postType !== 'post') {
                continue;
            }

            $status = $this->nsValue($item, $wpNs, 'status');
            if ($status !== 'publish') {
                $skipped++;
                continue;
            }

            $title = html_entity_decode(trim((string) $item->title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($title === '') {
                $skipped++;
                continue;
            }

            $wpId = $this->nsValue($item, $wpNs, 'post_id');
            $slug = $this->nsValue($item, $wpNs, 'post_name');
            $slug = $slug !== '' ? Str::slug(urldecode($slug)) : Str::slug($title);
            if ($slug === '') {
                $slug = 'wp-post-' . ($wpId ?: Str::lower(Str::random(8)));
            }

            $content = $this->encoded($item, $contentNs, 'content');

            $posts[] = [
                'title' => Str::limit($title, 250, ''),
                'slug' => $slug,
                'content' => $content !== '' ? $content : '<p></p>',
                'excerpt' => trim(strip_tags($this->encoded($item, $excerptNs, 'excerpt'))),
                'keywords' => $this->keywordsFromTags($item),
                'is_featured' => $this->nsValue($item, $wpNs, 'is_sticky') === '1',
                'published_at' => $this->publishedAt($item, $wpNs)->format('Y-m-d H:i:s'),
                'image_url' => $this->featuredImageUrl($item, $wpNs, $attachments)
                    ?: $this->firstContentImageUrl($content),
                'categories' => $this->categoryPayload($item),
            ];
        }

        return [
            'posts' => $posts,
            'skipped' => $skipped,
            'site_base_url' => $this->siteBaseUrl,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{done: bool, inserted: bool, changed: bool}
     */
    public function importPayload(array $payload, WordpressImportResult $result, bool $downloadImages = true): array
    {
        $this->result = $result;

        $slug = (string) ($payload['slug'] ?? '');
        $title = (string) ($payload['title'] ?? 'Post');
        $existing = Post::where('slug', $slug)->first();

        try {
            if ($existing) {
                if (! $downloadImages) {
                    return ['done' => true, 'inserted' => false, 'changed' => false];
                }

                $changed = $this->applyImagesToPost($existing, $payload);

                return [
                    'done' => ! $this->contentHasPendingImages((string) $existing->content),
                    'inserted' => false,
                    'changed' => $changed,
                ];
            }

            $originalContent = (string) ($payload['content'] ?? '<p></p>');
            $content = $downloadImages ? $this->rewriteContentImages($originalContent) : $originalContent;
            $cover = null;

            if ($downloadImages) {
                $coverUrl = $payload['image_url'] ?: $this->firstContentImageUrl($originalContent);
                $cover = $coverUrl ? $this->localizeImageUrl((string) $coverUrl) : null;
            }

            $post = new Post([
                'title' => $title,
                'slug' => $slug,
                'content' => $content !== '' ? $content : '<p></p>',
                'image' => $cover,
                'is_active' => true,
                'is_featured' => (bool) ($payload['is_featured'] ?? false),
                'meta_description' => filled($payload['excerpt'] ?? null) ? Str::limit((string) $payload['excerpt'], 160, '') : null,
                'meta_keywords' => $payload['keywords'] ?? null,
            ]);

            $published = Carbon::parse($payload['published_at'] ?? now());
            $post->created_at = $published;
            $post->updated_at = $published;
            $post->save();
            $post->blogCategories()->sync($this->syncCategoriesFromPayload($payload['categories'] ?? [], $result));

            return [
                'done' => ! $downloadImages || ! $this->contentHasPendingImages((string) $post->content),
                'inserted' => true,
                'changed' => true,
            ];
        } catch (Throwable $e) {
            $result->addError("“{$title}”: " . $e->getMessage());

            return ['done' => true, 'inserted' => false, 'changed' => false];
        }
    }

    public function setImageBudget(int $budget): self
    {
        $this->imageBudget = $budget;

        return $this;
    }

    public function setDeadline(float $timestamp): self
    {
        $this->deadlineAt = $timestamp;

        return $this;
    }

    /**
     * @param  list<string>  $roots
     */
    public function setUploadRoots(array $roots): self
    {
        $this->uploadRoots = array_values(array_filter($roots, fn ($root) => is_string($root) && $root !== '' && is_dir($root)));

        return $this;
    }

    /**
     * Pastas locais onde pode estar o wp-content/uploads do WordPress.
     *
     * @return list<string>
     */
    public static function resolveUploadRoots(?string $custom = null): array
    {
        $candidates = array_filter([
            $custom,
            storage_path('app/wp-uploads'),
            public_path('wp-content/uploads'),
            base_path('wp-content/uploads'),
            base_path('../wp-content/uploads'),
            base_path('../public/wp-content/uploads'),
            '/var/www/rnpet.com.br/wp-content/uploads',
            '/var/www/rnpet.com.br/public/wp-content/uploads',
            '/var/www/rnpet.com.br/petb2b/public/wp-content/uploads',
            '/var/www/rnpet.com.br/petb2b/wp-content/uploads',
        ]);

        $roots = [];
        foreach ($candidates as $candidate) {
            $real = realpath((string) $candidate);
            if ($real !== false && is_dir($real) && ! in_array($real, $roots, true)) {
                $roots[] = $real;
            }
        }

        return $roots;
    }

    public function pastDeadline(): bool
    {
        return $this->deadlineAt !== null && microtime(true) >= $this->deadlineAt;
    }

    public function setSiteBaseUrl(string $url): self
    {
        $this->siteBaseUrl = $url;

        return $this;
    }

    /**
     * @param  array<string, string|null>  $map
     */
    public function hydrateDownloads(array $map): self
    {
        $this->downloaded = $map;

        return $this;
    }

    /**
     * @return array<string, string|null>
     */
    public function downloadCache(): array
    {
        return $this->downloaded;
    }

    /**
     * Completa capa e fotos de posts já importados a partir da pasta uploads local.
     *
     * @return array{updated: int, scanned: int, images: int}
     */
    public function backfillExistingPosts(?string $uploadsPath = null): array
    {
        if ($this->uploadRoots === []) {
            $this->setUploadRoots(self::resolveUploadRoots($uploadsPath));
        }

        $this->imageBudget = PHP_INT_MAX;
        $this->deadlineAt = null;
        $this->downloaded = [];
        $this->result = new WordpressImportResult();

        if ($this->siteBaseUrl === '') {
            $appUrl = rtrim((string) config('app.url'), '/');
            $this->siteBaseUrl = ($appUrl === '' || str_contains($appUrl, 'localhost'))
                ? 'https://rnpet.com.br'
                : $appUrl;
        }

        $updated = 0;
        $scanned = 0;

        Post::query()
            ->where(function ($query) {
                $query->whereNull('image')
                    ->orWhere('image', '')
                    ->orWhere('content', 'like', '%wp-content/uploads%');
            })
            ->orderBy('id')
            ->chunkById(50, function ($posts) use (&$updated, &$scanned) {
                foreach ($posts as $post) {
                    $scanned++;
                    $payload = [
                        'content' => $post->content,
                        'image_url' => $this->firstContentImageUrl((string) $post->content),
                    ];
                    if ($this->applyImagesToPost($post, $payload)) {
                        $updated++;
                    }
                }
            });

        return [
            'updated' => $updated,
            'scanned' => $scanned,
            'images' => $this->result->imagesDownloaded,
        ];
    }

    private function loadXml(string $path): SimpleXMLElement
    {
        if (! is_readable($path)) {
            throw new \InvalidArgumentException('Não foi possível ler o arquivo XML.');
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            throw new \InvalidArgumentException('O arquivo XML está vazio.');
        }

        $raw = $this->normalizeWxr($raw);

        $xml = $this->parseRss($raw);
        if (! $this->findChannel($xml)) {
            throw new \InvalidArgumentException($this->invalidXmlMessage($raw, 'sem a tag <channel> do WordPress'));
        }

        return $xml;
    }

    private function normalizeWxr(string $raw): string
    {
        if (str_starts_with($raw, "\x1f\x8b")) {
            $decoded = @gzdecode($raw);
            if (is_string($decoded) && $decoded !== '') {
                $raw = $decoded;
            }
        }

        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        if (str_starts_with($raw, "\xFF\xFE") || str_starts_with($raw, "\xFE\xFF")) {
            $converted = @mb_convert_encoding($raw, 'UTF-8', 'UTF-16');
            if (is_string($converted) && $converted !== '') {
                $raw = $converted;
            }
        }

        if (! mb_check_encoding($raw, 'UTF-8')) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $raw);
            if (! is_string($converted) || $converted === '') {
                $converted = @mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
            }
            if (is_string($converted) && $converted !== '') {
                $raw = $converted;
            }
        }

        // Remove caracteres de controle inválidos em XML 1.0 (mantém tab/LF/CR).
        $raw = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $raw) ?? $raw;

        // WXR real costuma ter & solto em URLs e HTML fora de CDATA.
        return preg_replace('/&(?!#\d+;|#x[0-9a-fA-F]+;|[a-zA-Z][a-zA-Z0-9]+;)/', '&amp;', $raw) ?? $raw;
    }

    private function parseRss(string $raw): SimpleXMLElement
    {
        $flags = LIBXML_NONET | LIBXML_NOCDATA | LIBXML_COMPACT | LIBXML_PARSEHUGE;

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($raw, SimpleXMLElement::class, $flags);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($xml instanceof SimpleXMLElement) {
            return $xml;
        }

        $detail = isset($errors[0]) ? trim($errors[0]->message) : 'não foi possível interpretar o arquivo';

        throw new \InvalidArgumentException($this->invalidXmlMessage($raw, $detail));
    }

    private function findChannel(SimpleXMLElement $xml): ?SimpleXMLElement
    {
        if (isset($xml->channel) && $xml->channel instanceof SimpleXMLElement) {
            return $xml->channel;
        }

        if (strtolower($xml->getName()) === 'channel') {
            return $xml;
        }

        $matches = $xml->xpath('//*[local-name()="channel"]');

        return ($matches[0] ?? null) instanceof SimpleXMLElement ? $matches[0] : null;
    }

    /**
     * @return list<SimpleXMLElement>
     */
    private function items(SimpleXMLElement $channel): array
    {
        $list = [];

        if (isset($channel->item)) {
            foreach ($channel->item as $item) {
                $list[] = $item;
            }
        }

        if ($list !== []) {
            return $list;
        }

        $matches = $channel->xpath('.//*[local-name()="item"]') ?: [];

        return array_values(array_filter(
            $matches,
            fn ($item) => $item instanceof SimpleXMLElement
        ));
    }

    private function invalidXmlMessage(string $raw, string $detail): string
    {
        $start = ltrim($raw);
        $hint = 'Exporte em Ferramentas → Exportar → Posts.';

        if (preg_match('/^<(?:!DOCTYPE\s+)?html/i', $start) === 1) {
            $hint = 'O arquivo recebido parece HTML, não um XML do WordPress.';
        } elseif (! str_contains($raw, '<rss') && ! str_contains($raw, '<channel')) {
            $hint = 'Não encontramos as tags <rss>/<channel> de uma exportação WXR.';
        } elseif (! str_contains($raw, '</rss>') && ! str_contains($raw, '</channel>')) {
            $hint = 'O arquivo parece incompleto (cortado no upload). Aumente upload_max_filesize e post_max_size para 64M ou mais.';
        }

        return "XML inválido ({$detail}). {$hint}";
    }

    /**
     * @return array<string, string> wp post_id => attachment_url
     */
    private function attachmentMap(SimpleXMLElement $channel, string $wpNs): array
    {
        $map = [];

        foreach ($this->items($channel) as $item) {
            if ($this->nsValue($item, $wpNs, 'post_type') !== 'attachment') {
                continue;
            }

            $id = $this->nsValue($item, $wpNs, 'post_id');
            $url = $this->nsValue($item, $wpNs, 'attachment_url')
                ?: trim((string) ($item->guid ?? ''));
            if ($id !== '' && $url !== '') {
                $map[$id] = $url;
            }
        }

        return $map;
    }

    /**
     * @return list<array{name: string, slug: string}>
     */
    private function categoryPayload(SimpleXMLElement $item): array
    {
        $ids = [];

        foreach ($item->category as $category) {
            $domain = (string) $category['domain'];
            if ($domain !== '' && $domain !== 'category') {
                continue;
            }

            $name = html_entity_decode(trim((string) $category), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $slug = (string) $category['nicename'];
            $slug = $slug !== '' ? Str::slug(urldecode($slug)) : Str::slug($name);

            if ($name === '' && $slug === '') {
                continue;
            }

            if (in_array($slug, ['uncategorized', 'sem-categoria', 'sem-categorias'], true)) {
                $name = self::FALLBACK_CATEGORY['name'];
                $slug = self::FALLBACK_CATEGORY['slug'];
            }

            if ($name === '') {
                $name = Str::title(str_replace('-', ' ', $slug));
            }

            $ids[] = [
                'name' => Str::limit($name, 120, ''),
                'slug' => $slug ?: Str::slug($name),
            ];
        }

        return $ids;
    }

    /**
     * @param  list<array{name?: string, slug?: string}>  $categories
     * @return list<int>
     */
    private function syncCategoriesFromPayload(array $categories, WordpressImportResult $result): array
    {
        $ids = [];

        foreach ($categories as $category) {
            $slug = (string) ($category['slug'] ?? '');
            $name = (string) ($category['name'] ?? '');
            if ($slug === '' && $name === '') {
                continue;
            }
            if ($slug === '') {
                $slug = Str::slug($name);
            }

            $existing = BlogCategory::query()->where('slug', $slug)->first();
            if (! $existing) {
                $existing = BlogCategory::create([
                    'name' => Str::limit($name !== '' ? $name : Str::title(str_replace('-', ' ', $slug)), 120, ''),
                    'slug' => $slug,
                ]);
                $result->categoriesCreated++;
            }

            $ids[] = $existing->id;
        }

        if ($ids === []) {
            $fallback = BlogCategory::query()->firstOrCreate(
                ['slug' => self::FALLBACK_CATEGORY['slug']],
                ['name' => self::FALLBACK_CATEGORY['name']]
            );

            if ($fallback->wasRecentlyCreated) {
                $result->categoriesCreated++;
            }

            $ids[] = $fallback->id;
        }

        return array_values(array_unique($ids));
    }

    private function keywordsFromTags(SimpleXMLElement $item): ?string
    {
        $tags = [];

        foreach ($item->category as $category) {
            if ((string) $category['domain'] !== 'post_tag') {
                continue;
            }

            $name = html_entity_decode(trim((string) $category), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($name !== '') {
                $tags[] = $name;
            }
        }

        return $tags === [] ? null : Str::limit(implode(', ', $tags), 250, '');
    }

    /**
     * @param  array<string, string>  $attachments
     */
    private function featuredImageUrl(SimpleXMLElement $item, string $wpNs, array $attachments): ?string
    {
        $metas = $item->children($wpNs)->postmeta;
        if (! $metas || count($metas) === 0) {
            $metas = $item->children('wp', true)->postmeta;
        }

        foreach ($metas as $meta) {
            $key = $this->nsValue($meta, $wpNs, 'meta_key');
            $value = $this->nsValue($meta, $wpNs, 'meta_value');

            if ($key === '_thumbnail_id' && $value !== '' && isset($attachments[$value])) {
                return $attachments[$value];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyImagesToPost(Post $post, array $payload): bool
    {
        $originalContent = $post->content ?: (string) ($payload['content'] ?? '');
        $rewritten = $this->rewriteContentImages($originalContent);
        $changed = $rewritten !== $originalContent;

        if (! $post->image) {
            $coverUrl = $payload['image_url'] ?: $this->firstContentImageUrl($originalContent);
            $stored = $coverUrl ? $this->localizeImageUrl((string) $coverUrl) : null;
            if ($stored) {
                $post->image = $stored;
                $changed = true;
            }
        }

        if ($changed) {
            $post->content = $rewritten;
            $post->save();
        }

        return $changed;
    }

    private function contentHasPendingImages(string $html): bool
    {
        if (preg_match_all('/\b(?:src|data-src|data-orig-file|data-large-file)=[\'"]([^\'"]+)/i', $html, $matches) < 1) {
            return false;
        }

        foreach ($matches[1] as $rawUrl) {
            $url = html_entity_decode((string) $rawUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (str_contains($url, '/storage/blog/posts/')) {
                continue;
            }

            $absolute = $this->absoluteUrl($url);
            if ($absolute === null) {
                continue;
            }

            $canonical = $this->canonicalImageUrl($absolute);
            if (array_key_exists($canonical, $this->downloaded)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function rewriteContentImages(string $html): string
    {
        if ($html === '' || preg_match('/<img\b|srcset=/i', $html) !== 1) {
            return $html;
        }

        if (strlen($html) > 200_000) {
            return $html;
        }

        $html = preg_replace_callback(
            '/\b(src|data-src|data-orig-file|data-large-file|href)=([\'"])([^\'"]+)\2/i',
            function (array $match): string {
                $attr = strtolower($match[1]);
                $url = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');

                if ($attr === 'href' && ! $this->looksLikeImageUrl($url)) {
                    return $match[0];
                }

                $public = $this->publicImageUrl($url);

                return $public ? $match[1] . '=' . $match[2] . $public . $match[2] : $match[0];
            },
            $html
        ) ?? $html;

        return preg_replace_callback(
            '/\bsrcset=([\'"])([^\'"]+)\1/i',
            fn (array $match): string => 'srcset=' . $match[1] . $this->rewriteSrcset($match[2]) . $match[1],
            $html
        ) ?? $html;
    }

    private function rewriteSrcset(string $srcset): string
    {
        $parts = [];

        foreach (array_map('trim', explode(',', $srcset)) as $part) {
            if ($part === '') {
                continue;
            }

            $bits = preg_split('/\s+/', $part, 2) ?: [];
            $url = $bits[0] ?? '';
            $descriptor = $bits[1] ?? '';
            $public = $url !== '' ? $this->publicImageUrl(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')) : null;

            $parts[] = trim(($public ?: $url) . ' ' . $descriptor);
        }

        return implode(', ', $parts);
    }

    private function publicImageUrl(string $url): ?string
    {
        $stored = $this->localizeImageUrl($url);

        return $stored ? Storage::disk('public')->url($stored) : null;
    }

    private function localizeImageUrl(string $url): ?string
    {
        $absolute = $this->absoluteUrl($url);
        if ($absolute === null) {
            return null;
        }

        if (preg_match('~/storage/(blog/posts/[^?#]+)~', $absolute, $local)) {
            return $local[1];
        }

        $canonical = $this->canonicalImageUrl($absolute);
        if (array_key_exists($canonical, $this->downloaded)) {
            return $this->downloaded[$canonical];
        }

        if ($this->imageBudget <= 0 || $this->pastDeadline()) {
            return null;
        }

        $this->imageBudget--;
        $stored = $this->copyLocalUpload($canonical) ?? $this->downloadImage($canonical);
        $this->downloaded[$canonical] = $stored;
        $this->downloaded[$absolute] = $stored;

        if ($stored) {
            $this->result->imagesDownloaded++;
        } else {
            $this->result->imagesFailed++;
        }

        return $stored;
    }

    private function firstContentImageUrl(string $html): ?string
    {
        if (preg_match('/<img\b[^>]*\bsrc=[\'"]([^\'"]+)/i', $html, $match) !== 1) {
            return null;
        }

        return $this->absoluteUrl(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function channelBaseUrl(SimpleXMLElement $channel, string $wpNs): string
    {
        $raw = $this->nsValue($channel, $wpNs, 'base_blog_url')
            ?: $this->nsValue($channel, $wpNs, 'base_site_url')
            ?: trim((string) ($channel->link ?? ''));

        $parts = parse_url($raw);
        if (! isset($parts['scheme'], $parts['host'])) {
            return '';
        }

        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return strtolower($parts['scheme']) . '://' . strtolower($parts['host']) . $port;
    }

    private function absoluteUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, 'data:') || str_starts_with($url, 'blob:')) {
            return null;
        }

        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        } elseif (str_starts_with($url, '/') && $this->siteBaseUrl !== '') {
            $url = $this->siteBaseUrl . $url;
        } elseif (! preg_match('#^https?://#i', $url)) {
            return null;
        }

        return $this->isSafePublicUrl($url) ? $url : null;
    }

    private function canonicalImageUrl(string $url): string
    {
        $parts = parse_url($url);
        if (! isset($parts['scheme'], $parts['host'], $parts['path'])) {
            return $url;
        }

        $path = preg_replace('/-\d+x\d+(?=\.[a-zA-Z0-9]+$)/', '', $parts['path']) ?? $parts['path'];
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return $parts['scheme'] . '://' . $parts['host'] . $port . $path;
    }

    private function looksLikeImageUrl(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        return str_contains($path, '/wp-content/uploads/')
            || (bool) preg_match('/\.(jpe?g|png|gif|webp)$/', $path);
    }

    private function copyLocalUpload(string $url): ?string
    {
        if ($this->uploadRoots === []) {
            return null;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        if (preg_match('#/wp-content/uploads/(.+)$#i', $path, $match) !== 1) {
            return null;
        }

        $relative = ltrim(urldecode($match[1]), '/');
        $original = preg_replace('/-\d+x\d+(?=\.[a-zA-Z0-9]+$)/', '', $relative) ?? $relative;

        foreach ($this->uploadRoots as $root) {
            foreach (array_unique([$original, $relative]) as $file) {
                $absolute = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);
                if (! is_file($absolute) || ! is_readable($absolute)) {
                    continue;
                }

                $body = file_get_contents($absolute);
                if (! is_string($body) || $body === '') {
                    continue;
                }

                $ext = $this->imageExtension($body, '', $absolute);
                if ($ext === null) {
                    continue;
                }

                $stored = 'blog/posts/' . Str::uuid() . '.' . $ext;
                Storage::disk('public')->put($stored, $body);

                return $stored;
            }
        }

        return null;
    }

    private function isSelfHost(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }

        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $hosts = array_filter([
            $appHost,
            $appHost !== '' && str_starts_with($appHost, 'www.') ? substr($appHost, 4) : null,
            $appHost !== '' && ! str_starts_with($appHost, 'www.') ? 'www.'.$appHost : null,
            'rnpet.com.br',
            'www.rnpet.com.br',
        ]);

        return in_array($host, $hosts, true);
    }

    private function downloadImage(string $url): ?string
    {
        if (! $this->isSafePublicUrl($url) || $this->pastDeadline()) {
            return null;
        }

        // O site novo é o mesmo domínio do WP: pedir a URL por HTTP
        // devolve 404 (ou trava o PHP-FPM). Só serve arquivo local.
        if ($this->isSelfHost($url)) {
            return $this->copyLocalUpload($url);
        }

        try {
            $response = Http::timeout(4)
                ->connectTimeout(2)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; RevistaNegociosPet/1.0; +https://rnpet.com.br)',
                    'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer' => $this->siteBaseUrl !== '' ? $this->siteBaseUrl . '/' : $url,
                ])
                ->withOptions([
                    'allow_redirects' => ['max' => 3],
                    'curl' => [
                        CURLOPT_TIMEOUT => 4,
                        CURLOPT_CONNECTTIMEOUT => 2,
                        CURLOPT_NOSIGNAL => true,
                    ],
                ])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();
            if ($body === '' || strlen($body) > 8_000_000) {
                return null;
            }

            $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
            $ext = $this->imageExtension($body, $mime, $url);

            if ($ext === null) {
                return null;
            }

            $path = 'blog/posts/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, $body);

            return $path;
        } catch (Throwable) {
            return null;
        }
    }

    private function imageExtension(string $body, string $mime, string $url): ?string
    {
        $fromMagic = match (true) {
            str_starts_with($body, "\xFF\xD8\xFF") => 'jpg',
            str_starts_with($body, "\x89PNG") => 'png',
            str_starts_with($body, 'GIF8') => 'gif',
            str_starts_with($body, 'RIFF') && str_contains(substr($body, 0, 16), 'WEBP') => 'webp',
            default => null,
        };

        if ($fromMagic !== null) {
            return $fromMagic;
        }

        $fromMime = match (true) {
            str_contains($mime, 'jpeg'), str_contains($mime, 'jpg') => 'jpg',
            str_contains($mime, 'png') => 'png',
            str_contains($mime, 'webp') => 'webp',
            str_contains($mime, 'gif') => 'gif',
            default => null,
        };

        if ($fromMime !== null) {
            return $fromMime;
        }

        $fromUrl = strtolower((string) pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $fromUrl = $fromUrl === 'jpeg' ? 'jpg' : $fromUrl;

        return in_array($fromUrl, ['jpg', 'png', 'webp', 'gif'], true) ? $fromUrl : null;
    }

    private function isSafePublicUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (! isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parts['host']);
        if (in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)
            && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        return true;
    }

    private function publishedAt(SimpleXMLElement $item, string $wpNs): Carbon
    {
        $raw = $this->nsValue($item, $wpNs, 'post_date')
            ?: $this->nsValue($item, $wpNs, 'post_date_gmt')
            ?: trim((string) $item->pubDate);

        try {
            return $raw !== '' ? Carbon::parse($raw) : now();
        } catch (Throwable) {
            return now();
        }
    }

    private function nsValue(SimpleXMLElement $el, string $namespace, string $name): string
    {
        $value = $el->children($namespace)->{$name} ?? null;
        if ($value !== null && trim((string) $value) !== '') {
            return trim((string) $value);
        }

        $prefixed = $el->children('wp', true)->{$name} ?? null;

        return $prefixed === null ? '' : trim((string) $prefixed);
    }

    private function encoded(SimpleXMLElement $item, string $namespace, string $prefix): string
    {
        $value = (string) $item->children($namespace)->encoded;
        if ($value !== '') {
            return $value;
        }

        return (string) $item->children($prefix, true)->encoded;
    }

    /**
     * @param  array<string, string>  $namespaces
     */
    private function namespaceUri(array $namespaces, string $prefix, string $fallback): string
    {
        if (isset($namespaces[$prefix]) && $namespaces[$prefix] !== '') {
            return $namespaces[$prefix];
        }

        foreach ($namespaces as $uri) {
            if ($prefix === 'wp' && str_contains($uri, 'wordpress.org/export') && ! str_contains($uri, 'excerpt')) {
                return $uri;
            }
            if ($prefix === 'excerpt' && str_contains($uri, 'excerpt')) {
                return $uri;
            }
            if ($prefix === 'content' && str_contains($uri, 'modules/content')) {
                return $uri;
            }
        }

        return $fallback;
    }
}
