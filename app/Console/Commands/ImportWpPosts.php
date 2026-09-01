<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\BlogCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportWpPosts extends Command
{
    protected $signature = 'import:wp-posts';
    protected $description = 'Migra posts do WP liberando restricoes de chaves estrangeiras temporariamente e forcando as datas nativas';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $xmlPath = storage_path('app/wordpress.xml');

        $this->info('Iniciando leitura sequencial do arquivo wordpress.xml...');

        if (!file_exists($xmlPath)) {
            $this->error('Erro: O arquivo "wordpress.xml" nao foi encontrado em: ' . $xmlPath);
            return Command::FAILURE;
        }

        $this->info('Limpando dados antigos de posts e vinculos para reimportacao limpa...');

        // Desativa as foreign keys para permitir a limpeza segura sem estourar o erro 1701
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::table('blog_category_post')->truncate();
        Post::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $content = file_get_contents($xmlPath);
        preg_match_all('/<item>(.*?)<\/item>/s', $content, $items);

        // Mapeia as mídias/anexos
        $attachments = [];
        if (isset($items[1])) {
            foreach ($items[1] as $itemBlock) {
                if (str_contains($itemBlock, '<wp:post_type>attachment</wp:post_type>') || str_contains($itemBlock, '<wp:post_type><![CDATA[attachment]]></wp:post_type>')) {
                    preg_match('/<wp:post_id>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/wp:post_id>/', $itemBlock, $idMatch);
                    preg_match('/<guid(?:.*?)>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/guid>/', $itemBlock, $urlMatch);
                    $id = $idMatch[1] ?? null;
                    $url = $urlMatch[1] ?? null;
                    if ($id && $url) {
                        $attachments[trim($id)] = trim($url);
                    }
                }
            }
        }

        if (!isset($items[1])) {
            $this->error('Nenhum bloco <item> foi encontrado no arquivo.');
            return Command::FAILURE;
        }

        $postBlocks = [];
        foreach ($items[1] as $itemBlock) {
            if (str_contains($itemBlock, '<wp:post_type>post</wp:post_type>') || str_contains($itemBlock, '<wp:post_type><![CDATA[post]]></wp:post_type>')) {
                $postBlocks[] = $itemBlock;
            }
        }

        $total = count($postBlocks);
        $this->info("Total de materias filtradas para importacao: {$total}");

        $imported = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($postBlocks as $itemBlock) {
            preg_match('/<title>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/title>/s', $itemBlock, $titleMatch);
            preg_match('/<wp:post_name>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/wp:post_name>/s', $itemBlock, $slugMatch);
            preg_match('/<content:encoded>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/content:encoded>/s', $itemBlock, $contentMatch);
            preg_match('/<wp:status>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/wp:status>/s', $itemBlock, $statusMatch);

            // Busca a tag de data nativa limpa do WordPress
            preg_match('/<wp:post_date>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/wp:post_date>/s', $itemBlock, $dateMatch);

            $title = isset($titleMatch[1]) ? trim($titleMatch[1]) : 'Materia sem Titulo';
            $slug = isset($slugMatch[1]) ? trim($slugMatch[1]) : Str::slug($title);
            $postContent = isset($contentMatch[1]) ? trim($contentMatch[1]) : '';

            if (empty($postContent)) {
                preg_match('/<description>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/description>/s', $itemBlock, $descMatch);
                $postContent = isset($descMatch[1]) ? trim($descMatch[1]) : '';
            }

            if (empty($slug)) {
                $bar->advance();
                continue;
            }

            // Captura a categoria do post
            $categorySlug = 'geral';
            $categoryName = 'Geral';
            if (preg_match('/<category domain="category" nicename="(.*?)">(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/category>/s', $itemBlock, $catMatch)) {
                $categorySlug = trim($catMatch[1]);
                $categoryName = trim($catMatch[2]);
            }

            $blogCategory = BlogCategory::firstOrCreate(
                ['slug' => $categorySlug],
                ['name' => $categoryName]
            );

            // Mapeia imagem de destaque
            $localImagePath = null;
            if (preg_match('/<wp:meta_key>(?:<!\[CDATA\[)?_thumbnail_id(?:\]\]>)?<\/wp:meta_key>\s*<wp:meta_value>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/wp:meta_value>/s', $itemBlock, $thumbMatch)) {
                $thumbnailId = trim($thumbMatch[1]);
                if ($thumbnailId && isset($attachments[$thumbnailId])) {
                    $imageUrl = $attachments[$thumbnailId];
                    try {
                        $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
                        $imageLocalPath = 'public/posts/' . $filename;
                        $absoluteImagePath = storage_path('app/' . $imageLocalPath);

                        if (!file_exists(dirname($absoluteImagePath))) {
                            mkdir(dirname($absoluteImagePath), 0755, true);
                        }

                        if (!file_exists($absoluteImagePath)) {
                            $response = Http::timeout(3)->get($imageUrl);
                            if ($response->successful()) {
                                file_put_contents($absoluteImagePath, $response->body());
                                $localImagePath = 'posts/' . $filename;
                            }
                        } else {
                            $localImagePath = 'posts/' . $filename;
                        }
                    } catch (\Exception $e) {
                        $localImagePath = null;
                    }
                }
            }

            $wpStatus = isset($statusMatch[1]) ? trim($statusMatch[1]) : 'publish';
            $isActive = ($wpStatus === 'publish');

            // Garante o parse exato da data original do WordPress
            $createdAt = now();
            if (isset($dateMatch[1]) && trim($dateMatch[1]) !== '0000-00-00 00:00:00' && !empty(trim($dateMatch[1]))) {
                try {
                    $createdAt = Carbon::parse(trim($dateMatch[1]));
                } catch (\Exception $e) {
                    $createdAt = now();
                }
            }

            // CORREÇÃO CRÍTICA: Instancia um novo objeto da model e desativa temporariamente a automação de timestamps do Laravel
            $post = new Post();
            $post->timestamps = false;

            // Preenche as colunas manualmente garantindo que o banco receba a data original
            $post->title = $title;
            $post->slug = $slug;
            $post->content = $postContent;
            $post->is_active = $isActive;
            $post->is_featured = false;
            $post->image = $localImagePath;
            $post->created_at = $createdAt;
            $post->updated_at = $createdAt;
            $post->save();

            // Sincronização segura das chaves pivot Many-to-Many
            if ($post && isset($post->id)) {
                $post->blogCategories()->sync([$blogCategory->id]);
                $imported++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Sucesso! Foram importados {$imported} posts com as datas originais do WordPress travadas com sucesso.");

        return Command::SUCCESS;
    }
}
