<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Wordpress\WordpressXmlImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class WordpressImportController extends Controller
{
    public function __invoke(Request $request, WordpressXmlImporter $importer): RedirectResponse
    {
        if (! $request->hasFile('wordpress_xml')) {
            $phpMax = ini_get('upload_max_filesize') ?: '2M';

            return back()->with(
                'wordpress_import_error',
                "O arquivo não chegou ao servidor. O XML do WordPress costuma ser grande — o limite atual do PHP é {$phpMax}. Aumente upload_max_filesize e post_max_size para pelo menos 64M."
            );
        }

        $request->validate([
            'wordpress_xml' => 'required|file|max:102400',
            'download_images' => 'nullable',
        ], [
            'wordpress_xml.required' => 'Envie o arquivo XML exportado do WordPress.',
            'wordpress_xml.file' => 'Envie o arquivo XML exportado do WordPress.',
            'wordpress_xml.max' => 'O XML pode ter no máximo 100 MB.',
        ]);

        $file = $request->file('wordpress_xml');
        $original = strtolower((string) $file->getClientOriginalName());
        if (! str_ends_with($original, '.xml')) {
            return back()->with('wordpress_import_error', 'O arquivo precisa ser um XML (.xml) exportado pelo WordPress.');
        }

        @set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $result = $importer->import(
                $file->getRealPath(),
                $request->boolean('download_images')
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'wordpress_import_error',
                'Falha ao ler o XML: ' . $e->getMessage()
            );
        }

        if ($result->created === 0 && $result->skipped === 0 && $result->failed === 0) {
            return back()->with(
                'wordpress_import_error',
                'Nenhum post publicado foi encontrado no XML. Exporte em Ferramentas → Exportar → Posts (não só páginas).'
            );
        }

        return back()
            ->with('message', $result->summary())
            ->with('wordpress_import_errors', $result->errors);
    }
}
