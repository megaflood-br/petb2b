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
     * Mapeia os dados baseando-se nas chaves geradas pelo cabeçalho.
     * Aceita column1–column6 ou nomes comuns (categoria, uf, cidade, nome…).
     */
    public function model(array $row)
    {
        // Captura o nome da empresa
        $name = $this->cell($row, ['column4', 'nome', 'name', 'razao_social', 'empresa']);

        // Se a linha não tiver nome, pula o registro para não gerar erros
        if ($name === null) {
            return null;
        }

        // Captura a categoria escrita na planilha
        $categoryName = $this->cell($row, ['column1', 'categoria', 'category']) ?? 'geral';
        $categorySlug = Str::slug($categoryName);

        // AUTOMAÇÃO CRÍTICA: Se a categoria não existir no banco, cria ela na hora!
        Category::firstOrCreate(
            ['slug' => $categorySlug],
            ['name' => $categoryName]
        );

        // Captura o e-mail. Se estiver vazio ou inválido, gera um temporário único
        $emailRaw = $this->cell($row, ['column6', 'email', 'e_mail']);
        $email = ($emailRaw !== null && filter_var($emailRaw, FILTER_VALIDATE_EMAIL))
            ? $emailRaw
            : Str::slug($name) . '@temporario-' . Str::random(4) . '.com.br';

        $state = $this->cell($row, ['column2', 'uf', 'estado', 'state']);
        $city = $this->cell($row, ['column3', 'cidade', 'city']);
        $phoneRaw = $this->cell($row, ['column5', 'telefone', 'phone', 'fone']);

        // Limpa o telefone deixando apenas os números
        $phoneClean = $phoneRaw ? preg_replace('/[^0-9]/', '', $phoneRaw) : null;

        // Travas de segurança para respeitar o limite de tamanho dos campos no MySQL
        $state = $state !== null ? Str::limit(Str::upper($state), 2, '') : null;
        $phone = $phoneClean ? Str::limit($phoneClean, 15, '') : null;
        $whatsapp = $phone;

        // ANTI-DUPLICIDADE: Verifica se o e-mail já existe no banco local
        $existingSupplier = Supplier::where('email', $email)->first();

        if ($existingSupplier) {
            $existingSupplier->update([
                'name' => $name,
                'category' => $categorySlug,
                'state' => $state,
                'city' => $city,
                'phone' => $phone,
                'whatsapp' => $whatsapp,
            ]);
            return null;
        }

        $slug = Str::slug($name) . '-' . Str::lower(Str::random(5));

        return new Supplier([
            'name' => $name,
            'slug' => $slug,
            'email' => $email,
            'category' => $categorySlug,
            'state' => $state,
            'city' => $city,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'is_approved' => false,
            'is_active' => true,
            'description' => 'Empresa ainda não preencheu esta informação.',
            'address' => 'Endereço a completar',
        ]);
    }

    /**
     * Primeiro valor não vazio entre as chaves possíveis. Vazio permanece nulo.
     */
    private function cell(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $row) || $row[$key] === null) {
                continue;
            }

            $value = trim((string) $row[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
