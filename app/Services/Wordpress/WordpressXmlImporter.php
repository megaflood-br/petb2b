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

    public function import(string $path, bool $downloadImages = true): WordpressImportResult
    {
        $result = new WordpressImportResult();
        $xml = $this->loadXml($path);

        $namespaces = $xml->getDocNamespaces(true);
        $wpNs = $namespaces['wp'] ?? 'http://wordpress.org/export/1.2/';
        $contentNs = $namespaces['content'] ?? 'http://purl.org/rss/1.0/modules/content/';
        $excerptNs = $namespaces['excerpt'] ?? 'http://wordpress.org/export/1.2/excerpt/';

        $attachments = $this->attachmentMap($xml->channel, $wpNs);

        foreach ($xml->channel->item as $item) {
            $postType = $this->nsValue($item, $wpNs, 'post_type') ?: 'post';
            if ($postType !== 'post') {
                continue;
            }

            $status = $this->nsValue($item, $wpNs, 'status');
            if ($status !== 'publish') {
                $result->skipped++;
                continue;
            }

            $title = html_entity_decode(trim((string) $item->title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($title === '') {
                $result->skipped++;
                continue;
            }

            $wpId = $this->nsValue($item, $wpNs, 'post_id');
            $slug = $this->nsValue($item, $wpNs, 'post_name');
            $slug = $slug !== '' ? Str::slug(urldecode($slug)) : Str::slug($title);
            if ($slug === '') {
                $slug = 'wp-post-' . ($wpId ?: Str::lower(Str::random(8)));
            }

            if (Post::where('slug', $slug)->exists()) {
                $result->skipped++;
                continue;
            }

            try {
                $content = (string) $item->children($contentNs)->encoded;
                $excerpt = trim(strip_tags((string) $item->children($excerptNs)->encoded));

                $post = new Post([
                    'title' => Str::limit($title, 250, ''),
                    'slug' => $slug,
                    'content' => $content !== '' ? $content : '<p></p>',
                    'is_active' => true,
                    'is_featured' => $this->nsValue($item, $wpNs, 'is_sticky') === '1',
                    'meta_description' => $excerpt !== '' ? Str::limit($excerpt, 160, '') : null,
                    'meta_keywords' => $this->keywordsFromTags($item),
                ]);

                $published = $this->publishedAt($item, $wpNs);
                $post->created_at = $published;
                $post->updated_at = $published;
                $post->save();

                $categoryIds = $this->syncCategories($item, $result);
                $post->blogCategories()->sync($categoryIds);

                if ($downloadImages) {
                    $imageUrl = $this->featuredImageUrl($item, $wpNs, $attachments);
                    if ($imageUrl) {
                        $stored = $this->downloadImage($imageUrl);
                        if ($stored) {
                            $post->forceFill(['image' => $stored])->save();
                        }
                    }
                }

                $result->created++;
            } catch (Throwable $e) {
                $result->addError("“{$title}”: " . $e->getMessage());
            }
        }

        return $result;
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

        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($raw, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $xml instanceof SimpleXMLElement || ! isset($xml->channel)) {
            throw new \InvalidArgumentException('XML inválido. Use a exportação do WordPress (Ferramentas → Exportar).');
        }

        return $xml;
    }

    /**
     * @return array<string, string> wp post_id => attachment_url
     */
    private function attachmentMap(SimpleXMLElement $channel, string $wpNs): array
    {
        $map = [];

        foreach ($channel->item as $item) {
            if ($this->nsValue($item, $wpNs, 'post_type') !== 'attachment') {
                continue;
            }

            $id = $this->nsValue($item, $wpNs, 'post_id');
            $url = $this->nsValue($item, $wpNs, 'attachment_url');
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
        foreach ($item->children($wpNs)->postmeta as $meta) {
            $key = trim((string) $meta->children($wpNs)->meta_key);
            $value = trim((string) $meta->children($wpNs)->meta_value);

            if ($key === '_thumbnail_id' && $value !== '' && isset($attachments[$value])) {
                return $attachments[$value];
            }
        }

        return null;
    }

    private function downloadImage(string $url): ?string
    {
        if (! $this->isSafePublicUrl($url)) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => ['max' => 3]])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();
            if (strlen($body) < 32 || strlen($body) > 8_000_000) {
                return null;
            }

            $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
            $ext = match (true) {
                str_contains($mime, 'jpeg'), str_contains($mime, 'jpg') => 'jpg',
                str_contains($mime, 'png') => 'png',
                str_contains($mime, 'webp') => 'webp',
                str_contains($mime, 'gif') => 'gif',
                default => strtolower((string) pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)),
            };

            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                return null;
            }

            $path = 'blog/posts/' . Str::uuid() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
            Storage::disk('public')->put($path, $body);

            return $path;
        } catch (Throwable) {
            return null;
        }
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

        return $value === null ? '' : trim((string) $value);
    }
}
