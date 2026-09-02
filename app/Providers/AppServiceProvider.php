<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Models\BlogCategory;
use App\Services\Pix\AsaasPixGateway;
use App\Services\Pix\FakePixGateway;
use App\Services\Pix\PixGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Provedor PIX: usa o Asaas quando há API key configurada; caso
        // contrário, um driver Fake (dev local/testes sem credenciais).
        $this->app->bind(PixGateway::class, function () {
            $key = \App\Support\Settings::asaasKey();

            if (empty($key)) {
                return new FakePixGateway();
            }

            return new AsaasPixGateway(
                \App\Support\Settings::asaasBaseUrl(),
                (string) $key,
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // -----------------------------------------------------------------
        // CONTROLE DE ACESSO (GATES)
        // -----------------------------------------------------------------

        Gate::define('access-admin', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('access-supplier', function (User $user) {
            return $user->role === 'supplier';
        });

        Gate::define('access-breeder', function (User $user) {
            return $user->role === 'breeder';
        });


        // -----------------------------------------------------------------
        // COMPOSERS DE VIEWS (FILTRAGEM DE CATEGORIAS EDITORIAIS)
        // -----------------------------------------------------------------

        View::composer('layouts.app', function ($view) {
            // Executado em toda página; cacheado para evitar a query por request.
            // Invalidado em BlogCategory::saved/deleted (ver model).
            $categories = Cache::remember(BlogCategory::NAV_CACHE_KEY, now()->addHours(6), function () {
                // BLINDAGEM: remove termos de edição antiga ou tags órfãs do WordPress
                return BlogCategory::where('name', 'NOT LIKE', '%Edição%')
                    ->where('name', 'NOT LIKE', '%Ed.%')
                    ->where('name', 'NOT LIKE', '%Destaques%')
                    ->where('name', 'NOT LIKE', '%Conteúdo Privado%')
                    ->where('name', 'NOT LIKE', '%Corona%')
                    ->whereNotIn('slug', ['geral', 'uncategorized'])
                    ->orderBy('name', 'asc')
                    ->get();
            });

            $view->with('blogCategories', $categories);
        });
    }
}
