<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Classified;
use App\Models\Event;
use App\Models\Magazine;
use App\Models\Post;
use App\Models\ProductReview;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /** Chave e TTL do cache das seções da home. */
    public const CACHE_KEY = 'home.sections';
    private const CACHE_TTL_SECONDS = 300; // 5 minutos

    public function __invoke()
    {
        // Blocos de leitura (pesados) cacheados para não rodarem 8+ queries a
        // cada acesso à página mais visitada do portal.
        $sections = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return [
                'latestMagazine' => Magazine::where('is_active', true)->latest()->first(),

                'latestPosts' => Post::where('is_active', true)
                    ->orderBy('is_featured', 'desc')
                    ->latest()
                    ->take(9)
                    ->get(),

                'raceSuppliers' => Supplier::where('is_approved', true)
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->where('category', 'racas')
                          ->orWhere('category', 'canis')
                          ->orWhere('category', 'adestradores');
                    })
                    ->latest()
                    ->take(3)
                    ->get(),

                'featuredSuppliers' => Supplier::where('is_approved', true)
                    ->where('is_active', true)
                    ->where('is_verified', true)
                    ->latest()
                    ->take(3)
                    ->get(),

                'topCategories' => Supplier::select('category', DB::raw('count(*) as total'))
                    ->where('is_approved', true)
                    ->whereNotNull('category')
                    ->groupBy('category')
                    ->orderBy('total', 'desc')
                    ->take(4)
                    ->get(),

                'featuredClassifieds' => Classified::where('is_active', true)
                    ->with('supplier')
                    ->latest()
                    ->take(3)
                    ->get(),

                'upcomingEvents' => Event::where('is_active', true)
                    ->where('start_date', '>=', now())
                    ->orderBy('start_date', 'asc')
                    ->take(2)
                    ->get(),

                'featuredReviews' => ProductReview::where('is_active', true)
                    ->latest()
                    ->take(2)
                    ->get(),
            ];
        });

        // Banner do topo: sorteio + registro de impressão precisam rodar a cada
        // requisição, então ficam FORA do cache.
        $bannerHome = Advertisement::where('is_active', true)
            ->where('position', 'banner_topo')
            ->inRandomOrder()
            ->first();

        if ($bannerHome) {
            $bannerHome->trackImpression();
        }

        return view('welcome', array_merge($sections, [
            'bannerHome' => $bannerHome,
        ]));
    }
}
