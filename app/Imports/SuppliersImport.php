<?php

namespace App\Imports;

use App\Models\Supplier;
use App\Models\Category; // Garante o acesso à Model de Categorias
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Str;

class SuppliersImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    /**
     * Mapeia os dados baseando-se estritamente nas chaves geradas pelo cabeçalho:
     * column1 = categoria, column2 = uf, column3 = cidade, column4 = nome, column5 = telefone, column6 = email
     */
    public function model(array $row)
    {
        // Captura o nome da empresa na chave exata gerada pela planilha (column4)
        $name = $row['column4'] ?? null;

        // Se a linha não tiver nome, pula o registro para não gerar erros
        if (empty($name) || trim($name) == '') {
            return null;
        }

        // Captura a categoria escrita na planilha (column1)
        $categoryRaw = $row['column1'] ?? 'geral';
        $categoryName = trim($categoryRaw);
        $categorySlug = Str::slug($categoryName);

        // AUTOMAÇÃO CRÍTICA: Se a categoria não existir no banco, cria ela na hora!
        if (!empty($categoryName)) {
            Category::firstOrCreate(
                ['slug' => $categorySlug], // Chave de busca para evitar duplicados
                ['name' => $categoryName]  // Dados que serão gravados se for nova
            );
        }

        // Captura o e-mail (column6). Se estiver vazio ou inválido, gera um temporário único
        $emailRaw = $row['column6'] ?? null;
        $email = (!empty($emailRaw) && filter_var(trim($emailRaw), FILTER_VALIDATE_EMAIL))
            ? trim($emailRaw)
            : Str::slug($name) . '@temporario-' . Str::random(4) . '.com.br';

        // Captura as demais colunas seguindo o cabeçalho do seu Excel
        $state    = $row['column2'] ?? 'SP';
        $city     = $row['column3'] ?? 'Atibaia';
        $phoneRaw = $row['column5'] ?? null;

        // Limpa o telefone deixando apenas os números
        $phoneClean = $phoneRaw ? preg_replace('/[^0-9]/', '', $phoneRaw) : null;

        // Travas de segurança para respeitar o limite de tamanho dos campos no MySQL
        $state    = Str::limit(Str::upper(trim($state)), 2, ''); // Garante no máximo 2 caracteres
        $phone    = $phoneClean ? Str::limit($phoneClean, 15, '') : null;
        $whatsapp = $phone; // Copia o número para o campo do WhatsApp

        // ANTI-DUPLICIDADE: Verifica se o e-mail já existe no banco local
        $existingSupplier = Supplier::where('email', $email)->first();

        if ($existingSupplier) {
            // Se já existir, atualiza os dados do fornecedor antigo
            $existingSupplier->update([
                'name'     => trim($name),
                'category' => $categorySlug, // Salva o slug da categoria vinculada
                'state'    => empty($state) ? 'SP' : $state,
                'city'     => empty($city) ? 'Atibaia' : trim($city),
                'phone'    => $phone,
                'whatsapp' => $whatsapp,
            ]);
            return null;
        }

        // Se o e-mail for inédito, gera um slug novo e faz o insert padrão seguro
        $slug = Str::slug($name) . '-' . Str::lower(Str::random(5));

        return new Supplier([
            'name'          => trim($name),
            'slug'          => $slug,
            'email'         => $email,
            'category'      => $categorySlug, // Salva o slug da categoria vinculada
            'state'         => empty($state) ? 'SP' : $state,
            'city'          => empty($city) ? 'Atibaia' : trim($city),
            'phone'         => $phone,
            'whatsapp'      => $whatsapp,
            'is_approved'   => false,
            'is_active'     => true,
            'description'   => 'Empresa ainda não preencheu esta informação.',
            'address'       => 'Endereço a completar',
        ]);
    }
}
