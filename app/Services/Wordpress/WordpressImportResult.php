<?php

namespace App\Services\Wordpress;

class WordpressImportResult
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public int $failed = 0;
    public int $categoriesCreated = 0;
    public int $imagesDownloaded = 0;
    public int $imagesFailed = 0;

    /** @var list<string> */
    public array $errors = [];

    public function addError(string $message): void
    {
        if (count($this->errors) < 10) {
            $this->errors[] = $message;
        }

        $this->failed++;
    }

    public function summary(): string
    {
        $parts = [
            "{$this->created} " . ($this->created === 1 ? 'post criado' : 'posts criados'),
        ];

        if ($this->updated > 0) {
            $parts[] = "{$this->updated} " . ($this->updated === 1 ? 'atualizado com imagens' : 'atualizados com imagens');
        }

        $parts[] = "{$this->skipped} " . ($this->skipped === 1 ? 'já existia' : 'já existiam');

        if ($this->categoriesCreated > 0) {
            $parts[] = "{$this->categoriesCreated} " . ($this->categoriesCreated === 1 ? 'categoria nova' : 'categorias novas');
        }

        if ($this->imagesDownloaded > 0) {
            $parts[] = "{$this->imagesDownloaded} " . ($this->imagesDownloaded === 1 ? 'imagem baixada' : 'imagens baixadas');
        }

        if ($this->imagesFailed > 0) {
            $parts[] = "{$this->imagesFailed} " . ($this->imagesFailed === 1 ? 'imagem não baixada' : 'imagens não baixadas');
        }

        if ($this->failed > 0) {
            $parts[] = "{$this->failed} " . ($this->failed === 1 ? 'falha' : 'falhas');
        }

        return 'Importação concluída: ' . implode(', ', $parts) . '.';
    }
}
