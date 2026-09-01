<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Categories;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. USUÁRIO ADMINISTRADOR
        User::create([
            'name' => 'Carlos Admin',
            'email' => 'carlos@megaflood.com.br',
            'password' => Hash::make('master21'),
            'role' => 'admin',
        ]);

        // 2. FORNECEDOR B2B
        User::create([
            'name' => 'Fornecedor de Teste',
            'email' => 'fornecedor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'supplier',
        ]);

        // 3. CANIL / CRIADOR
        User::create([
            'name' => 'Criador de Teste',
            'email' => 'canil@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'breeder',
        ]);

        // CORREÇÃO: Popula a tabela de categorias para alimentar o Select B2B
        $categories = [
            ['name' => 'Alimentos e Rações', 'slug' => 'alimentos-e-racoes'],
            ['name' => 'Acessórios e Brinquedos', 'slug' => 'acessorios-e-brinquedos'],
            ['name' => 'Equipamentos para Banho e Tosa', 'slug' => 'equipamentos-banho-tosa'],
            ['name' => 'Medicamentos e Veterinária', 'slug' => 'medicamentos-e-veterinaria'],
            ['name' => 'Cosméticos e Higiene Pet', 'slug' => 'cosmeticos-e-higiene-pet'],
        ];

        foreach ($categories as $cat) {
            // Ajuste o nome da tabela se sua model de categorias usar outro nome (ex: 'categories')
            DB::table('categories')->updateOrInsert(['slug' => $cat['slug']], $cat);
        }
    }
}
