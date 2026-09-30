<?php

namespace App\Console\Commands;

use App\Services\SitemapBuilder;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Gera o sitemap com as páginas públicas indexáveis do portal';

    public function handle(SitemapBuilder $builder): int
    {
        $builder->writeToPublic();
        $this->info('Sitemap gerado em public/sitemap.xml');

        return self::SUCCESS;
    }
}
