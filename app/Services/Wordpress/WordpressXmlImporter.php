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

    public function import(string $path, bool $downloadImages = true): WordpressImportResult
    {
        $this->result = new WordpressImportResult();
        $this->downloaded = [];

        $xml = $this->loadXml($path);
        $channel = $this->findChannel($xml);

        $namespaces = $xml->getDocNamespaces(true) + $channel->getDocNamespaces(true);
        $wpNs = $this->namespaceUri($namespaces, 'wp', 'http://wordpress.org/export/1.2/');
        $contentNs = $this->namespaceUri($namespaces, 'content', 'http://purl.org/rss/1.0/modules/content/');
        $excerptNs = $this->namespaceUri($namespaces, 'excerpt', 'http://wordpress.org/export/1.2/excerpt/');

        $this->siteBaseUrl = $this->channelBaseUrl($channel, $wpNs);
        $attachments = $this->attachmentMap($channel, $wpNs);

        foreach ($this->items($channel) as $item) {
            $postType = $this->nsValue($item, $wpNs, 'post_type') ?: 'post';
            if ($postType !== 'post') {
                continue;
            }

            $status = $this->nsValue($item, $wpNs, 'status');
            if ($status !== 'publish') {
                $this->result->skipped++;
                continue;
            }

            $title = html_entity_decode(trim((string) $item->title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($title === '') {
                $this->result->skipped++;
                continue;
            }

            $wpId = $this->nsValue($item, $wpNs, 'post_id');
            $slug = $this->nsValue($item, $wpNs, 'post_name');
            $slug = $slug !== '' ? Str::slug(urldecode($slug)) : Str::slug($title);
            if ($slug === '') {
                $slug = 'wp-post-' . ($wpId ?: Str::lower(Str::random(8)));
            }

            $existing = Post::where('slug', $slug)->first();
            if ($existing) {
                if ($downloadImages) {
                    try {
                        $this->refreshPostImages($existing, $item, $wpNs, $contentNs, $attachments);
                    } catch (Throwable $e) {
                        $this->result->addError("“{$title}”: " . $e->getMessage());
                    }
                } else {
                    $this->result->skipped++;
                }
                continue;
            }

            try {
                $originalContent = $this->encoded($item, $contentNs, 'content');
                $content = $originalContent;
                $excerpt = trim(strip_tags($this->encoded($item, $excerptNs, 'excerpt')));
                $cover = null;

                if ($downloadImages) {
                    $content = $this->rewriteContentImages($originalContent);
                    $coverUrl = $this->featuredImageUrl($item, $wpNs, $attachments)
                        ?: $this->firstContentImageUrl($originalContent);
                    $cover = $coverUrl ? $this->localizeImageUrl($coverUrl) : null;
                }

                $post = new Post([
                    'title' => Str::limit($title, 250, ''),
                    'slug' => $slug,
                    'content' => $content !== '' ? $content : '<p></p>',
                    'image' => $cover,
                    'is_active' => true,
                    'is_featured' => $this->nsValue($item, $wpNs, 'is_sticky') === '1',
                    'meta_description' => $excerpt !== '' ? Str::limit($excerpt, 160, '') : null,
                    'meta_keywords' => $this->keywordsFromTags($item),
                ]);

                $published = $this->publishedAt($item, $wpNs);
                $post->created_at = $published;
                $post->updated_at = $published;
                $post->save();

                $categoryIds = $this->syncCategories($item, $this->result);
                $post->blogCategories()->sync($categoryIds);

                $this->result->created++;
            } catch (Throwable $e) {
                $this->result->addError("“{$title}”: " . $e->getMessage());
            }
        }

        return $this->result;
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
     * @return list<int>
     */
    private function syncCategories(SimpleXMLElement $item, WordpressImportResult $result): array
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

            $existing = BlogCategory::query()->where('slug', $slug)->first();
            if (! $existing) {
                $existing = BlogCategory::create([
                    'name' => Str::limit($name, 120, ''),
                    'slug' => $slug ?: Str::slug($name),
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
     * @param  array<string, string>  $attachments
     */
    private function refreshPostImages(
        Post $post,
        SimpleXMLElement $item,
        string $wpNs,
        string $contentNs,
        array $attachments
    ): void {
        $originalContent = $post->content ?: $this->encoded($item, $contentNs, 'content');
        $rewritten = $this->rewriteContentImages($originalContent);
        $changed = $rewritten !== $originalContent;

        if (! $post->image) {
            $coverUrl = $this->featuredImageUrl($item, $wpNs, $attachments)
                ?: $this->firstContentImageUrl($originalContent);
            $stored = $coverUrl ? $this->localizeImageUrl($coverUrl) : null;
            if ($stored) {
                $post->image = $stored;
                $changed = true;
            }
        }

        if (! $changed) {
            $this->result->skipped++;

            return;
        }

        $post->content = $rewritten;
        $post->save();
        $this->result->updated++;
    }

    private function rewriteContentImages(string $html): string
    {
        if ($html === '' || preg_match('/<img\b|srcset=/i', $html) !== 1) {
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

        $stored = $this->downloadImage($canonical);
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

    private function downloadImage(string $url): ?string
    {
        if (! $this->isSafePublicUrl($url)) {
            return null;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; RevistaNegociosPet/1.0; +https://rnpet.com.br)',
                    'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer' => $this->siteBaseUrl !== '' ? $this->siteBaseUrl . '/' : $url,
                ])
                ->withOptions(['allow_redirects' => ['max' => 5]])
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
