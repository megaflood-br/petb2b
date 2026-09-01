<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Models\BlogCategory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
            // BLINDAGEM: Caça e remove qualquer termo de edição antiga ou tags órfãs do WordPress
            $categories = BlogCategory::where('name', 'NOT LIKE', '%Edição%')
                ->where('name', 'NOT LIKE', '%Ed.%')
                ->where('name', 'NOT LIKE', '%Destaques%')
                ->where('name', 'NOT LIKE', '%Conteúdo Privado%')
                ->where('name', 'NOT LIKE', '%Corona%')
                ->whereNotIn('slug', ['geral', 'uncategorized'])
                ->orderBy('name', 'asc')
                ->get();

            $view->with('blogCategories', $categories);
        });
    }
}
