<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class SupplierCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Adestradores', 'Aquários', 'Associações', 'Atacadistas', 'Aviculturas',
            'Banho e Tosa', 'Clínicas', 'Consultorias', 'Distribuidores', 'Pet Sitter',
            'Entidades', 'Creches e Hotéis', 'Laboratórios', 'Passeadores', 'Pet Shops',
            'Representantes', 'Taxidog'
        ];

        foreach ($categories as $categoryName) {
            Category::updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName]
            );
        }

        $this->command->info('Tabela de categorias populada com sucesso!');
    }
}
