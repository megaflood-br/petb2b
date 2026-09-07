<?php

namespace App\Console\Commands;

use App\Services\Wordpress\WordpressXmlImporter;
use App\Support\Settings;
use Illuminate\Console\Command;

class BackfillWordpressImages extends Command
{
    protected $signature = 'wordpress:backfill-images {--uploads= : Caminho da pasta wp-content/uploads}';

    protected $description = 'Copia capas e fotos dos posts a partir da pasta local do WordPress';

    public function handle(WordpressXmlImporter $importer): int
    {
        $path = trim((string) $this->option('uploads'));
        if ($path === '') {
            $path = (string) Settings::get('wordpress_uploads_path', '/var/www/rnpet.com.br/wp-content/uploads');
        }

        if ($path !== '' && ! is_dir($path)) {
            $this->error("Pasta não encontrada: {$path}");

            return self::FAILURE;
        }

        if ($path !== '') {
            Settings::set('wordpress_uploads_path', $path);
            $importer->setUploadRoots(WordpressXmlImporter::resolveUploadRoots($path));
        }

        $this->info('Lendo imagens em ' . ($path !== '' ? $path : 'pastas detectadas') . '…');

        $result = $importer->backfillExistingPosts($path !== '' ? $path : null);

        $this->info("Posts lidos: {$result['scanned']}");
        $this->info("Posts atualizados: {$result['updated']}");
        $this->info("Imagens copiadas: {$result['images']}");

        return self::SUCCESS;
    }
}
