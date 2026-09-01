<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Supplier;
use App\Models\Classified; // Importante para os anúncios
// use App\Models\Page; // Descomente se você tiver uma model de Páginas Institucionais

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Gera o sitemap completo: Home, Fornecedores, Classificados e Páginas';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // 1. PÁGINAS ESTÁTICAS E HOME
        $sitemap->add(Url::create('/')
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        $sitemap->add(Url::create('/fornecedores')
            ->setPriority(0.9)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));

        $sitemap->add(Url::create('/classificados')
            ->setPriority(0.9)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        // 2. FORNECEDORES APROVADOS
        Supplier::where('is_approved', true)
            ->where('is_active', true)
            ->get()
            ->each(function (Supplier $supplier) use ($sitemap) {
                $sitemap->add(
                    Url::create("/fornecedores/{$supplier->slug}")
                        ->setPriority(0.8)
                        ->setLastModificationDate($supplier->updated_at)
                );
            });

        // 3. CLASSIFICADOS / ANÚNCIOS ATIVOS
        // Se a sua model de classificados tiver slug ou ID, ajuste a URL abaixo
        Classified::where('is_active', true)
            ->get()
            ->each(function (Classified $ad) use ($sitemap) {
                $sitemap->add(
                    Url::create("/classificados/{$ad->id}") // ou $ad->slug se você usar
                        ->setPriority(0.7)
                        ->setLastModificationDate($ad->updated_at)
                );
            });

        // 4. PÁGINAS INSTITUCIONAIS (Sobre, Contato, etc)
        // Se você não tiver uma model Page, pode adicionar manualmente:
        $sitemap->add(Url::create('/sobre-nos')->setPriority(0.5));
        $sitemap->add(Url::create('/contato')->setPriority(0.5));

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap completo gerado em public/sitemap.xml!');
    }
}
