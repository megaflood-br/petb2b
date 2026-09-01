<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Rações', 'Medicamentos', 'Brinquedos', 'Higiene', 'Equipamentos'];

        $suppliers = [
            ['name' => 'Distribuidora Pet Brasil', 'cat' => 'Rações'],
            ['name' => 'Laboratório VetVida', 'cat' => 'Medicamentos'],
            ['name' => 'Alpha Dog Equipamentos', 'cat' => 'Equipamentos'],
            ['name' => 'Boutique Animal Atacado', 'cat' => 'Brinquedos'],
            ['name' => 'CleanPet Higiene Profissional', 'cat' => 'Higiene'],
            ['name' => 'NutriPet Premium', 'cat' => 'Rações'],
            ['name' => 'VetTech Suprimentos', 'cat' => 'Equipamentos'],
            ['name' => 'Indústria de Ossos São Lazaro', 'cat' => 'Brinquedos'],
            ['name' => 'Shampoo & Cia B2B', 'cat' => 'Higiene'],
            ['name' => 'MedBicho Distribuidora', 'cat' => 'Medicamentos'],
        ];

        foreach ($suppliers as $item) {
            Supplier::create([
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'document' => '00.000.000/0001-00',
                'email' => Str::slug($item['name']) . '@exemplo.com.br',
                'phone' => '(11) 9999-9999',
                'website' => 'https://www.' . Str::slug($item['name']) . '.com.br',
                'description' => 'Líder no segmento de ' . $item['cat'] . ' atendendo todo o território nacional com foco em lojistas e clínicas.',
                'category' => $item['cat'],
                'seo_title' => $item['name'] . ' | Fornecedor Pet B2B Brasil',
                'seo_description' => 'Encontre os melhores produtos de ' . $item['cat'] . ' da empresa ' . $item['name'] . ' no portal Pet Business Pro.',
                'is_verified' => rand(0, 1),
                'is_active' => true,
            ]);
        }
    }
}
