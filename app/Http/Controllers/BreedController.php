<?php

namespace App\Http\Controllers;

use App\Models\Breed;
use App\Models\Post;

class BreedController extends Controller
{
    /**
     * Detalhe em /racas/{slug}.
     *
     * Resolve primeiro uma raça do guia; se não existir, cai para um artigo
     * legado publicado sob esse mesmo padrão de URL (o site antigo em WordPress
     * lançava raças como matérias na categoria "racas", ex.: /racas/{slug}).
     * Assim tanto o guia de raças quanto o conteúdo importado funcionam e o SEO
     * das URLs antigas é preservado.
     */
    public function show(string $slug)
    {
        $breed = Breed::where('slug', $slug)->where('is_active', true)->first();

        if ($breed) {
            return view('breeds.show', compact('breed'));
        }

        $post = Post::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return app(BlogController::class)->renderPost($post);
    }
}
