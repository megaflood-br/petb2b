<?php

namespace App\Support;

use App\Models\Breed;
use App\Models\Classified;
use App\Models\Event;
use App\Models\JobPosting;
use App\Models\Kennel;
use App\Models\Magazine;
use App\Models\Post;
use App\Models\ProductReview;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class FavoriteCatalog
{
    public const MATERIAS = 'materias';
    public const ANALISES = 'analises';
    public const CLASSIFICADOS = 'classificados';
    public const FORNECEDORES = 'fornecedores';
    public const VAGAS = 'vagas';
    public const REVISTAS = 'revistas';
    public const RACAS = 'racas';
    public const CANIS = 'canis';
    public const FEIRAS = 'feiras';

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    public static function folders(): array
    {
        return [
            self::MATERIAS => ['label' => 'Matérias', 'icon' => '📰'],
            self::ANALISES => ['label' => 'Análises', 'icon' => '⭐'],
            self::CLASSIFICADOS => ['label' => 'Classificados', 'icon' => '🏷️'],
            self::FORNECEDORES => ['label' => 'Fornecedores', 'icon' => '🏭'],
            self::VAGAS => ['label' => 'Vagas', 'icon' => '💼'],
            self::REVISTAS => ['label' => 'Revistas', 'icon' => '📖'],
            self::RACAS => ['label' => 'Raças', 'icon' => '🐾'],
            self::CANIS => ['label' => 'Canis', 'icon' => '🏠'],
            self::FEIRAS => ['label' => 'Feiras Pet', 'icon' => '📅'],
        ];
    }

    public static function label(string $folder): string
    {
        return self::folders()[$folder]['label'] ?? ucfirst($folder);
    }

    public static function icon(string $folder): string
    {
        return self::folders()[$folder]['icon'] ?? '♡';
    }

    /**
     * @return list<class-string<Model>>
     */
    public static function types(): array
    {
        return [
            Post::class,
            Classified::class,
            Supplier::class,
            JobPosting::class,
            Magazine::class,
            Breed::class,
            Kennel::class,
            Event::class,
            ProductReview::class,
        ];
    }

    public static function supports(Model $model): bool
    {
        return in_array($model::class, self::types(), true);
    }

    public static function folder(Model $model): string
    {
        return match (true) {
            $model instanceof Post => $model->isProductAnalysis() ? self::ANALISES : self::MATERIAS,
            $model instanceof ProductReview => self::ANALISES,
            $model instanceof Classified => self::CLASSIFICADOS,
            $model instanceof Supplier => self::FORNECEDORES,
            $model instanceof JobPosting => self::VAGAS,
            $model instanceof Magazine => self::REVISTAS,
            $model instanceof Breed => self::RACAS,
            $model instanceof Kennel => self::CANIS,
            $model instanceof Event => self::FEIRAS,
            default => self::MATERIAS,
        };
    }

    public static function title(Model $model): string
    {
        if ($model instanceof Breed) {
            return (string) $model->name;
        }

        if ($model instanceof Supplier || $model instanceof Kennel) {
            return (string) $model->name;
        }

        return (string) ($model->title ?? $model->name ?? 'Item');
    }

    public static function url(Model $model): ?string
    {
        return match (true) {
            $model instanceof Post => $model->isProductAnalysis()
                ? route('reviews.show', $model->slug)
                : $model->publicUrl(),
            $model instanceof ProductReview => route('reviews.show', $model->slug),
            $model instanceof Classified => route('classifieds.show', $model->slug),
            $model instanceof Supplier => route('suppliers.show', $model->slug),
            $model instanceof JobPosting => route('jobs.show', $model->slug),
            $model instanceof Magazine => route('magazines.show', $model->slug),
            $model instanceof Breed => route('breeds.show', $model->slug),
            $model instanceof Kennel => route('kennels.show', $model->slug),
            $model instanceof Event => route('events.index', ['slug' => $model->slug]),
            default => null,
        };
    }

    public static function imageUrl(Model $model): ?string
    {
        return match (true) {
            $model instanceof Post => $model->coverUrl(),
            $model instanceof ProductReview => self::storageUrl($model->image ?? null),
            $model instanceof Classified => self::storageUrl($model->image ?? null),
            $model instanceof Supplier => self::storageUrl($model->logo ?? null),
            $model instanceof Magazine => $model->coverUrl(),
            $model instanceof Breed => self::storageUrl($model->image ?? null),
            $model instanceof Kennel => self::storageUrl($model->cover_image ?? $model->logo ?? null),
            $model instanceof Event => self::storageUrl($model->image ?? null),
            default => null,
        };
    }

    private static function storageUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $path = ltrim((string) $path, '/');
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
