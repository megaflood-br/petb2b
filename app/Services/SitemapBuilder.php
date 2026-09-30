<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\Breed;
use App\Models\Classified;
use App\Models\Event;
use App\Models\JobPosting;
use App\Models\Kennel;
use App\Models\Magazine;
use App\Models\Post;
use App\Models\ProductReview;
use App\Models\Supplier;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapBuilder
{
    public function build(): Sitemap
    {
        $sitemap = Sitemap::create();

        $this->add($sitemap, url('/'), 1.0, Url::CHANGE_FREQUENCY_DAILY);
        $this->add($sitemap, route('blog.index'), 0.9, Url::CHANGE_FREQUENCY_DAILY);
        $this->add($sitemap, route('suppliers.index'), 0.9, Url::CHANGE_FREQUENCY_WEEKLY);
        $this->add($sitemap, route('classifieds.index'), 0.8, Url::CHANGE_FREQUENCY_DAILY);
        $this->add($sitemap, route('jobs.index'), 0.8, Url::CHANGE_FREQUENCY_DAILY);
        $this->add($sitemap, route('breeds.index'), 0.8, Url::CHANGE_FREQUENCY_WEEKLY);
        $this->add($sitemap, route('kennels.index'), 0.8, Url::CHANGE_FREQUENCY_WEEKLY);
        $this->add($sitemap, route('events.index'), 0.7, Url::CHANGE_FREQUENCY_WEEKLY);
        $this->add($sitemap, route('reviews.index'), 0.7, Url::CHANGE_FREQUENCY_WEEKLY);
        $this->add($sitemap, route('magazines.index'), 0.6, Url::CHANGE_FREQUENCY_WEEKLY);
        $this->add($sitemap, route('about'), 0.4, Url::CHANGE_FREQUENCY_YEARLY);
        $this->add($sitemap, route('contact'), 0.4, Url::CHANGE_FREQUENCY_YEARLY);
        $this->add($sitemap, route('advertise'), 0.4, Url::CHANGE_FREQUENCY_YEARLY);

        BlogCategory::query()->orderBy('name')->each(function (BlogCategory $category) use ($sitemap) {
            $this->add(
                $sitemap,
                route('blog.category', $category->slug),
                0.6,
                Url::CHANGE_FREQUENCY_WEEKLY,
                $category->updated_at
            );
        });

        Post::query()->where('is_active', true)->with('blogCategories')->latest('updated_at')
            ->each(function (Post $post) use ($sitemap) {
                $this->add($sitemap, $post->publicUrl(), 0.8, Url::CHANGE_FREQUENCY_WEEKLY, $post->updated_at);
            });

        Supplier::query()->where('is_approved', true)->where('is_active', true)
            ->each(function (Supplier $supplier) use ($sitemap) {
                $this->add($sitemap, route('suppliers.show', $supplier->slug), 0.7, Url::CHANGE_FREQUENCY_WEEKLY, $supplier->updated_at);
            });

        Classified::query()->where('is_active', true)->whereNotNull('slug')
            ->each(function (Classified $ad) use ($sitemap) {
                $this->add($sitemap, route('classifieds.show', $ad->slug), 0.6, Url::CHANGE_FREQUENCY_WEEKLY, $ad->updated_at);
            });

        JobPosting::query()->where('is_active', true)->whereNotNull('slug')
            ->each(function (JobPosting $job) use ($sitemap) {
                $this->add($sitemap, route('jobs.show', $job->slug), 0.6, Url::CHANGE_FREQUENCY_WEEKLY, $job->updated_at);
            });

        Breed::query()->where('is_active', true)
            ->each(function (Breed $breed) use ($sitemap) {
                $this->add($sitemap, route('breeds.show', $breed->slug), 0.6, Url::CHANGE_FREQUENCY_MONTHLY, $breed->updated_at);
            });

        Kennel::query()->where('is_active', true)
            ->each(function (Kennel $kennel) use ($sitemap) {
                $this->add($sitemap, route('kennels.show', $kennel->slug), 0.6, Url::CHANGE_FREQUENCY_WEEKLY, $kennel->updated_at);
            });

        Event::query()->where('is_active', true)
            ->each(function (Event $event) use ($sitemap) {
                $this->add($sitemap, route('events.index', ['slug' => $event->slug]), 0.5, Url::CHANGE_FREQUENCY_WEEKLY, $event->updated_at);
            });

        Magazine::query()->where('is_active', true)
            ->each(function (Magazine $magazine) use ($sitemap) {
                $this->add($sitemap, route('magazines.show', $magazine->slug), 0.5, Url::CHANGE_FREQUENCY_MONTHLY, $magazine->updated_at);
            });

        ProductReview::query()->where('is_active', true)
            ->each(function (ProductReview $review) use ($sitemap) {
                $this->add($sitemap, route('reviews.show', $review->slug), 0.6, Url::CHANGE_FREQUENCY_MONTHLY, $review->updated_at);
            });

        return $sitemap;
    }

    public function xml(): string
    {
        return $this->build()->render();
    }

    public function writeToPublic(): void
    {
        $this->build()->writeToFile(public_path('sitemap.xml'));
    }

    private function add(
        Sitemap $sitemap,
        string $url,
        float $priority,
        string $frequency,
        $lastmod = null
    ): void {
        $tag = Url::create($url)
            ->setPriority($priority)
            ->setChangeFrequency($frequency);

        if ($lastmod) {
            $tag->setLastModificationDate($lastmod);
        }

        $sitemap->add($tag);
    }
}
