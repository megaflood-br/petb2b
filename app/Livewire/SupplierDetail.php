<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\Classified;
use Livewire\Component;
use Artesaos\SEOTools\Facades\SEOTools;

class SupplierDetail extends Component
{
    public $slug;

    public function mount($slug)
    {
        $this->slug = $slug;
    }

    public function render()
    {
        // Puxa o fornecedor ativo correspondente ao slug visitado
        $supplier = Supplier::where('slug', $this->slug)->where('is_active', true)->firstOrFail();

        // Puxa os classificados vinculados a este fornecedor específico
        $classifieds = Classified::where('supplier_id', $supplier->id)->where('is_active', true)->get();

        // INJEÇÃO AUTOMÁTICA DE SEO:
        // Puxa as tags calculadas no banco de dados. Caso estejam vazias (registros antigos), gera um padrão profissional.
        $title = $supplier->seo_title ?? "{$supplier->name} | Fornecedor Pet em {$supplier->city} - {$supplier->state}";
        $description = $supplier->seo_description ?? "Conheça {$supplier->name} no Guia B2B da Revista Negócios Pet. Veja produtos, serviços e contatos rápidos.";

        // Alimenta o motor do SEOTools para renderizar no {!! SEO::generate() !!} do seu layout
        SEOTools::setTitle($title);
        SEOTools::setDescription($description);
        SEOTools::opengraph()->setUrl(request()->url());
        SEOTools::opengraph()->addProperty('type', 'business.business');

        if ($supplier->logo) {
            SEOTools::opengraph()->addImage(asset('storage/' . $supplier->logo));
        }

        // Retorna a view acoplando os dados
        return view('livewire.supplier-detail', compact('supplier', 'classifieds'))
            ->layout('layouts.app'); // Garante que usa o layout que você me enviou
    }
}
