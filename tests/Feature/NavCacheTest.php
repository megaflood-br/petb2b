<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class NavCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_salvar_categoria_invalida_o_cache_do_menu(): void
    {
        Cache::put(BlogCategory::NAV_CACHE_KEY, 'valor-antigo', now()->addHour());

        BlogCategory::create(['name' => 'Nutrição', 'slug' => 'nutricao']);

        $this->assertNull(Cache::get(BlogCategory::NAV_CACHE_KEY));
    }

    public function test_deletar_categoria_invalida_o_cache_do_menu(): void
    {
        $cat = BlogCategory::create(['name' => 'Saúde', 'slug' => 'saude']);
        Cache::put(BlogCategory::NAV_CACHE_KEY, 'valor-antigo', now()->addHour());

        $cat->delete();

        $this->assertNull(Cache::get(BlogCategory::NAV_CACHE_KEY));
    }
}
