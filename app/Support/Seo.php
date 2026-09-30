<?php

namespace App\Support;

use App\Models\Post;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Str;

/**
 * SEO de página pública: defaults do painel, tags por rota e JSON-LD.
 */
class Seo
{
    /** @var array<string, mixed>|null */
    private static ?array $article = null;

    public static function applyDefaults(): void
    {
        $site = Settings::siteName();
        $description = Settings::seoDescription();
        $keywords = Settings::seoKeywords();

        config([
            'seotools.meta.defaults.title' => $site,
            'seotools.meta.defaults.description' => $description,
            'seotools.meta.defaults.keywords' => $keywords,
            'seotools.meta.defaults.canonical' => 'current',
            'seotools.meta.defaults.robots' => 'index, follow',
            'seotools.opengraph.defaults.title' => $site,
            'seotools.opengraph.defaults.description' => $description,
            'seotools.opengraph.defaults.site_name' => $site,
            'seotools.opengraph.defaults.url' => null,
            'seotools.opengraph.defaults.type' => 'website',
            'seotools.json-ld.defaults.title' => $site,
            'seotools.json-ld.defaults.description' => $description,
            'seotools.json-ld.defaults.url' => null,
        ]);

        SEOTools::opengraph()->setSiteName($site);
        SEOTools::opengraph()->setUrl(url()->current());
        SEOTools::metatags()->setCanonical(url()->current());
        SEOTools::metatags()->setRobots('index, follow');

        if ($keywords !== []) {
            SEOTools::metatags()->addKeyword($keywords);
        }

        if ($image = Settings::ogImageUrl()) {
            SEOTools::opengraph()->addImage($image);
            SEOTools::twitter()->setImage($image);
        }

        if ($handle = Settings::twitterHandle()) {
            SEOTools::twitter()->addValue('site', $handle);
        }
    }

    public static function page(string $title, ?string $description = null, ?string $image = null): void
    {
        SEOTools::setTitle($title, false);
        SEOTools::opengraph()->setTitle($title);
        SEOTools::twitter()->setTitle($title);

        if ($description !== null && $description !== '') {
            SEOTools::setDescription($description);
            SEOTools::opengraph()->setDescription($description);
            SEOTools::twitter()->setDescription($description);
        }

        SEOTools::opengraph()->setUrl(url()->current());
        SEOTools::metatags()->setCanonical(url()->current());

        if ($image) {
            SEOTools::opengraph()->addImage($image);
            SEOTools::twitter()->setImage($image);
        }
    }

    public static function noindex(): void
    {
        SEOTools::metatags()->setRobots('noindex, follow');
    }

    public static function article(Post $post): void
    {
        $description = $post->meta_description ?: Str::limit(strip_tags((string) $post->content), 160, '');
        $image = $post->coverUrl() ?: Settings::ogImageUrl();

        self::$article = array_filter([
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $description,
            'datePublished' => optional($post->created_at)?->toIso8601String(),
            'dateModified' => optional($post->updated_at)?->toIso8601String(),
            'mainEntityOfPage' => url()->current(),
            'image' => $image,
            'author' => [
                '@type' => 'Organization',
                'name' => Settings::siteName(),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => Settings::siteName(),
                'logo' => array_filter([
                    '@type' => 'ImageObject',
                    'url' => Settings::ogImageUrl() ?: Settings::faviconUrl(),
                ]),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    public static function jsonLdGraph(): array
    {
        $site = Settings::siteName();
        $url = rtrim((string) config('app.url'), '/');
        $logo = Settings::ogImageUrl() ?: Settings::faviconUrl();
        $sameAs = Settings::socialProfiles();

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $url.'#organization',
            'name' => $site,
            'url' => $url !== '' ? $url : url('/'),
            'logo' => $logo,
            'sameAs' => $sameAs !== [] ? $sameAs : null,
        ]);

        $website = [
            '@type' => 'WebSite',
            '@id' => ($url !== '' ? $url : url('/')).'#website',
            'url' => $url !== '' ? $url : url('/'),
            'name' => $site,
            'description' => Settings::seoDescription(),
            'publisher' => ['@id' => $url.'#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/busca').'?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];

        $graph = [$organization, $website];
        if (self::$article) {
            $graph[] = self::$article;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }
}
