<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

// Facades de SEO
use Artesaos\SEOTools\Facades\SEOTools;

// Models
use App\Models\Supplier;
use App\Models\Lead;
use App\Models\Classified;
use App\Models\Event;
use App\Models\ProductReview;
use App\Models\Post;
use App\Models\Magazine;
use App\Models\ContactMessage;
use App\Models\Kennel;
use App\Models\Advertisement;

// Controllers & Livewire Públicos
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Livewire\JobList;
use App\Livewire\Supplier\ManageJobs;
use App\Livewire\Supplier\ManageSponsoredPosts;
use App\Livewire\SupplierList;
use App\Livewire\SupplierDetail;
use App\Livewire\ClassifiedsIndex;
use App\Livewire\ProductReviewsIndex;
use App\Livewire\ProductReviewShow;
use App\Livewire\EventList;
use App\Livewire\GeneralSearch;
use App\Livewire\ClassifiedShow;
use App\Livewire\ContactForm;
use App\Livewire\KennelList;

// Controllers & Livewire do Fornecedor (Supplier)
use App\Livewire\Supplier\ManageAds;

// Livewire Admin Master
use App\Livewire\Admin\ApproveSuppliers;
use App\Livewire\Admin\ManageReviews;
use App\Livewire\Admin\ManageBlog;
use App\Livewire\Admin\ManageMagazines;
use App\Livewire\Admin\ManageContacts;
use App\Livewire\Admin\ManageAds as AdminManageAds;
use App\Livewire\Admin\ManageCategories;
use App\Livewire\Admin\ManageKennels;

// -------------------------------------------------------------------
// 1. ÁREA PÚBLICA DO PORTAL
// -------------------------------------------------------------------

// Rota Pública de Redirecionamento de Anúncios (Contabiliza clique e desconta crédito)
Route::get('/ads/redirect/{advertisement}', function (Advertisement $advertisement) {
    $advertisement->trackClick();
    return redirect()->away($advertisement->link);
})->name('ads.redirect');

// Webhook de confirmação de pagamento PIX (Asaas). Validação por token no
// header 'asaas-access-token' dentro do controller; isento de CSRF.
Route::post('/webhooks/asaas', AsaasWebhookController::class)->name('webhooks.asaas');

// -------------------------------------------------------------------
// HOME PAGE DINÂMICA DA REVISTA NEGÓCIOS PET (ATUALIZADA E CORRIGIDA)
// -------------------------------------------------------------------
Route::get('/', function () {
    $latestMagazine = Magazine::where('is_active', true)->latest()->first();

    // Carrega os 6 posts mais recentes do blog para a seção "Destaques da Edição"
    $latestPosts = Post::where('is_active', true)
        ->orderBy('is_featured', 'desc')
        ->latest()
        ->take(9)
        ->get();

    // CORREÇÃO DEFINITIVA: Filtra os fornecedores diretamente pela coluna 'category' com o slug gerado pelo Excel
    $raceSuppliers = Supplier::where('is_approved', true)
        ->where('is_active', true)
        ->where(function($q) {
            $q->where('category', 'racas')
              ->orWhere('category', 'canis')
              ->orWhere('category', 'adestradores'); // Fallback seguro com os dados que você importou
        })
        ->latest()
        ->take(3)
        ->get();

    $featuredSuppliers = Supplier::where('is_approved', true)
        ->where('is_active', true)
        ->where('is_verified', true)
        ->latest()
        ->take(3)
        ->get();

    $topCategories = Supplier::select('category', DB::raw('count(*) as total'))
        ->where('is_approved', true)
        ->whereNotNull('category')
        ->groupBy('category')
        ->orderBy('total', 'desc')
        ->take(4)
        ->get();

    $featuredClassifieds = Classified::where('is_active', true)
        ->with('supplier')->latest()->take(3)->get();

    $upcomingEvents = Event::where('is_active', true)
        ->where('start_date', '>=', now())
        ->orderBy('start_date', 'asc')->take(2)->get();

    $featuredReviews = ProductReview::where('is_active', true)
        ->latest()->take(2)->get();

    $bannerHome = Advertisement::where('is_active', true)
        ->where('position', 'banner_topo')
        ->inRandomOrder()
        ->first();

    if ($bannerHome) {
        $bannerHome->trackImpression();
    }

    return view('welcome', compact(
        'latestPosts',
        'latestMagazine',
        'featuredSuppliers',
        'topCategories',
        'upcomingEvents',
        'featuredClassifieds',
        'featuredReviews',
        'bannerHome',
        'raceSuppliers' // Injetando a variável limpa na View
    ));
})->name('home');

// Seleção de Tipo de Cadastro (Canil vs Fornecedor)
Route::get('/cadastro-selecao', function () {
    return view('auth.register-select');
})->name('register.select');

// Módulos de Busca, Eventos e Criadores Públicos
Route::get('/busca', GeneralSearch::class)->name('general.search');
Route::get('/classificados', ClassifiedsIndex::class)->name('classifieds.index');
Route::get('/classificados/{classified:slug}', ClassifiedShow::class)->name('classifieds.show');

// Guia de Empregos / Vagas
Route::get('/vagas', JobList::class)->name('jobs.index');
Route::get('/vagas/{slug}', function ($slug) {
    $job = \App\Models\JobPosting::where('slug', $slug)
        ->where('is_active', true)
        ->with('supplier')
        ->firstOrFail();

    return view('jobs.show', compact('job'));
})->name('jobs.show');

// Agenda Pet apontando diretamente para o componente reativo do Livewire
Route::get('/feiras-pet-2026/{slug?}', \App\Livewire\EventList::class)->name('events.index');

Route::get('/canis', KennelList::class)->name('kennels.index');

// Perfil Interno do Canil (Blade Estático)
Route::get('/canis/{slug}', function ($slug) {
    $kennel = Kennel::where('slug', $slug)->where('is_active', true)->firstOrFail();
    return view('blog.kennel-show', compact('kennel'));
})->name('kennels.show');

// Estante de Revistas (Banca Digital)
Route::get('/revistas', function () {
    $magazines = Magazine::where('is_active', true)->latest()->get();
    SEOTools::setTitle('Estante de Revistas Digitais - Pet Business Pro');
    SEOTools::setDescription('Acesse todas as edições da nossa revista digital sobre o mercado pet brasileiro.');
    return view('magazines.index', compact('magazines'));
})->name('magazines.index');

// Leitor de Revista (Slug Amigável)
Route::get('/revista/{magazine:slug}', function (Magazine $magazine) {
    return view('magazines.show', compact('magazine'));
})->name('magazines.show');

// Reviews e Análises Técnicas
Route::get('/analises-produtos', ProductReviewsIndex::class)->name('reviews.index');
Route::get('/analise/{slug}', ProductReviewShow::class)->name('reviews.show');

// Guia de Fornecedores e Marcas
Route::prefix('fornecedores')->group(function () {
    Route::get('/', SupplierList::class)->name('suppliers.index');
    Route::get('/{slug}', SupplierDetail::class)->name('suppliers.show');
});

// Canal de Notícias Central (Geral)
Route::get('/noticias', function (Request $request) {
    SEOTools::setTitle('Notícias e Novidades Pet');
    SEOTools::setDescription('Fique por dentro das últimas notícias do mercado pet em Atibaia.');

    $blogCategories = \App\Models\BlogCategory::orderBy('name', 'asc')->get();
    $query = Post::where('is_active', true)->with('blogCategories');

    if ($request->has('categoria') && !empty($request->categoria)) {
        $query->whereHas('blogCategories', function ($q) use ($request) {
            $q->where('slug', $request->categoria);
        });
    }

    $posts = $query->orderBy('is_featured', 'desc')->latest()->paginate(6);
    $posts->appends(['categoria' => $request->categoria]);

    return view('blog.index', compact('posts', 'blogCategories'));
})->name('blog.index');

// Rota de Listagem de Categorias do WordPress: /categoria/{slug}
Route::get('/categoria/{slug}', function (Request $request, $slug) {
    $category = \App\Models\BlogCategory::where('slug', $slug)->firstOrFail();

    SEOTools::setTitle('Artigos sobre ' . $category->name);
    SEOTools::setDescription('Confira matérias e conteúdos estratégicos sobre ' . $category->name);

    $blogCategories = \App\Models\BlogCategory::orderBy('name', 'asc')->get();

    $posts = Post::where('is_active', true)
        ->whereHas('blogCategories', function ($q) use ($slug) {
            $q->where('slug', $slug);
        })
        ->orderBy('is_featured', 'desc')
        ->latest()
        ->paginate(6);

    $request->merge(['categoria' => $slug]);

    return view('blog.index', compact('posts', 'blogCategories'));
})->name('blog.category');

// Páginas Institucionais
Route::view('/sobre-nos', 'pages.about')->name('about');
Route::get('/contato', function() { return view('pages.contact'); })->name('contact');
Route::get('/anuncie-conosco', function () { return view('pages.advertise'); })->name('advertise');

// -------------------------------------------------------------------
// 2. ÁREAS RESTRITAS AUTENTICADAS (MIDDLEWARE MIDDLEMAN)
// -------------------------------------------------------------------

Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('profile', 'profile')->name('profile');

    // Redirecionamento Inteligente Único Pós-Login de acordo com a Role
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'supplier') {
            return redirect()->route('supplier.dashboard');
        }

        if ($user->role === 'breeder') {
            $hasKennel = Kennel::where('user_id', $user->id)->exists();
            if (!$hasKennel) {
                return redirect()->route('breeder.setup');
            }
            return redirect()->route('breeder.dashboard');
        }

        return redirect()->route('home');
    })->name('dashboard');

    // 2.1 PAINEL DO CRIADOR DE CANIL (BREEDER)
    Route::middleware(['can:access-breeder'])->prefix('meu-canil')->group(function () {
        Route::get('/configurar', \App\Livewire\Breeder\KennelSetup::class)->name('breeder.setup');
        Route::get('/dashboard', function () {
            $kennel = Kennel::where('user_id', Auth::id())->with('images')->firstOrFail();
            return view('pages.breeder.dashboard', compact('kennel'));
        })->name('breeder.dashboard');
    });

    // 2.2 PAINEL MASTER ADMINISTRATIVO (ADMIN)
    Route::middleware(['can:access-admin'])->prefix('admin')->group(function () {

        Route::get('/dashboard', function () {
            $stats = [
                'pending_suppliers' => Supplier::where('is_verified', false)->count(),
                'total_reviews'     => ProductReview::count(),
                'total_leads'       => Lead::count(),
                'total_posts'       => Post::count(),
                'unread_messages'   => ContactMessage::where('is_read', false)->count(),
                'latest_magazines'  => Magazine::latest()->take(3)->get(),
                'upcoming_events'   => Event::where('start_date', '>=', now())->orderBy('start_date', 'asc')->take(3)->get(),
            ];
            return view('admin.dashboard', compact('stats'));
        })->name('admin.dashboard');

        Route::get('/anuncios', AdminManageAds::class)->name('admin.ads');
        Route::get('/fornecedores', ApproveSuppliers::class)->name('admin.suppliers');
        Route::get('/blog', ManageBlog::class)->name('admin.blog');
        Route::get('/mensagens', ManageContacts::class)->name('admin.messages');
        Route::get('/revistas', ManageMagazines::class)->name('admin.magazines');
        Route::get('/reivindicacoes', \App\Livewire\Admin\ManageClaims::class)->name('admin.claims');
        Route::get('/categorias', ManageCategories::class)->name('admin.categories');
        Route::get('/canis', ManageKennels::class)->name('admin.kennels');

        Route::get('/eventos', function () { return view('admin.events.index'); })->name('admin.events');
        Route::get('/analises', function() { return view('admin.reviews.index'); })->name('admin.reviews');
    });

    // 2.3 PAINEL EXCLUSIVO DO FORNECEDOR (SUPPLIER)
    Route::middleware(['can:access-supplier'])->prefix('minha-empresa')->group(function () {
        Route::get('/dashboard', function () {
            $supplier = Supplier::where('user_id', Auth::id())->first();
            $stats = [
                'views' => $supplier->views ?? 0,
                'leads' => $supplier ? Lead::where('supplier_id', $supplier->id)->count() : 0,
                'unread_leads' => $supplier ? Lead::where('supplier_id', $supplier->id)->where('is_read', false)->count() : 0,
            ];
            return view('pages.supplier', compact('stats'));
        })->name('supplier.dashboard');

        Route::get('/mensagens', function () { return view('pages.supplier-leads'); })->name('supplier.messages');
        Route::get('/classificados', function() { return view('pages.supplier-classifieds'); })->name('supplier.classifieds');
        Route::get('/banners-e-creditos', ManageAds::class)->name('supplier.ads');
        Route::get('/vagas', ManageJobs::class)->name('supplier.jobs');
        Route::get('/materias', ManageSponsoredPosts::class)->name('supplier.sponsored');
    });

    // Logout Seguro da Conta
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

// -------------------------------------------------------------------
// ROTA DO ARTIGO DO WORDPRESS (SEO COMPATÍVEL COM YOAST)
// -------------------------------------------------------------------
Route::get('/{prefixCategory}/{slug}', function ($prefixCategory, $slug) {
    $post = Post::where('slug', $slug)->where('is_active', true)->firstOrFail();

    SEOTools::setTitle($post->title . ' | Revista Negócios Pet');
    SEOTools::setDescription($post->meta_description ?? Str::limit(strip_tags($post->content), 150));

    if (!empty($post->meta_keywords)) {
        SEOTools::metatags()->addKeyword($post->meta_keywords);
    }

    SEOTools::opengraph()->setUrl(url()->current());
    SEOTools::opengraph()->addProperty('type', 'article');
    SEOTools::opengraph()->setTitle($post->title);
    SEOTools::opengraph()->setDescription($post->meta_description ?? Str::limit(strip_tags($post->content), 150));

    if ($post->image) {
        SEOTools::opengraph()->addImage(asset('storage/' . $post->image));
    }

    SEOTools::twitter()->setTitle($post->title);
    SEOTools::twitter()->setDescription($post->meta_description ?? Str::limit(strip_tags($post->content), 150));
    if ($post->image) {
        SEOTools::twitter()->setImage(asset('storage/' . $post->image));
    }

    $relatedPosts = Post::where('is_active', true)
        ->where('id', '!=', $post->id)
        ->with('blogCategories')
        ->inRandomOrder()
        ->take(3)
        ->get();

    return view('blog.show', compact('post', 'relatedPosts'));
})->where('prefixCategory', '^(?!(admin|minha-empresa|meu-canil|fornecedores|noticias|categoria|busca|classificados|vagas|agenda-pet|canis|revistas|revista|analises-produtos|analise|sobre-nos|contato|anuncie-conosco|profile|dashboard|login|register|logout)$)[a-z0-9\-]+')->name('blog.show');

require __DIR__.'/auth.php';
