<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\ProductReview;
use App\Models\Post; // Importante
use App\Models\Magazine; // Importante
use App\Models\Event; // Importante
use App\Models\ContactMessage; // Importante
use App\Models\Lead; // Se você tiver um model de Leads para os fornecedores

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            // Contadores Básicos
            'pending_suppliers' => Supplier::where('is_verified', false)->count(),
            'total_reviews'     => ProductReview::count(),

            // Novas Chaves Solicitadas
            'total_posts'       => Post::count(),
            'unread_messages'   => ContactMessage::where('is_read', false)->count(),
            'total_leads'       => Supplier::sum('leads_count') ?? 0, // Ajuste conforme sua tabela

            // Listagens para os Widgets
            'latest_magazines'  => Magazine::latest()->take(3)->get(),
            'upcoming_events'   => Event::where('start_date', '>=', now())
                                    ->orderBy('start_date', 'asc')
                                    ->take(3)
                                    ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
