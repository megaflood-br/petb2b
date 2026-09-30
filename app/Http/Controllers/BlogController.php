<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Seo;
use App\Support\Settings;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Exibe um artigo no padrão de URL do WordPress (/{categoria}/{slug}).
     *
     * Esta é a rota "catch-all" de dois segmentos. Para não tratar como artigo
     * uma seção real do site (ex.: /admin/x, /minha-empresa/x), os prefixos
     * reservados são derivados dinamicamente dos routes registrados — assim,
     * adicionar novas seções não exige manter uma lista de exclusão manual.
     */
    public function show(string $prefixCategory, string $slug)
    {
        if (in_array($prefixCategory, $this->reservedSegments(), true)) {
            abort(404);
        }

        $post = Post::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return $this->renderPost($post);
    }

    /**
     * Renderiza a página do artigo (SEO + relacionados). Reutilizado por outras
     * rotas que também podem servir artigos legados (ex.: /racas/{slug}).
     */
    public function renderPost(Post $post)
    {
        $description = $post->meta_description ?? Str::limit(strip_tags($post->content), 150);
        $image = $post->hasCover() ? $post->coverUrl() : null;

        Seo::page($post->title.' | '.Settings::siteName(), $description, $image);
        if (! empty($post->meta_keywords)) {
            SEOTools::metatags()->addKeyword($post->meta_keywords);
        }
        SEOTools::opengraph()->addProperty('type', 'article');
        Seo::article($post);

        $relatedPosts = Post::where('is_active', true)
            ->where('id', '!=', $post->id)
            ->with('blogCategories')
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * Primeiros segmentos literais de todas as rotas registradas (ex.: admin,
     * fornecedores, vagas, racas...). Usado para não confundir uma seção do
     * site com um slug de artigo.
     *
     * @return array<int,string>
     */
    private function reservedSegments(): array
    {
        return collect(Route::getRoutes())
            ->map(fn ($route) => explode('/', $route->uri())[0])
            ->filter(fn ($segment) => $segment !== '' && ! str_contains($segment, '{'))
            ->unique()
            ->values()
            ->all();
    }
}
