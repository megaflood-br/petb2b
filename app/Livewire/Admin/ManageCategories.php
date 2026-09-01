<?php

namespace App\Livewire\Admin;

use App\Models\BlogCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

class ManageCategories extends Component
{
    use WithPagination;

    public $name, $categoryId;
    public $showForm = false;

    protected $rules = [
        'name' => 'required|min:2|unique:blog_categories,name',
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.manage-categories', [
            'categories' => BlogCategory::orderBy('name')->paginate(10)
        ]);
    }

    public function toggleForm()
    {
        $this->reset(['name', 'categoryId']);
        $this->showForm = !$this->showForm;
    }

    public function save()
    {
        // Se estiver editando, ignora o próprio ID na validação de nome único
        if ($this->categoryId) {
            $this->validate([
                'name' => 'required|min:2|unique:blog_categories,name,' . $this->categoryId,
            ]);
        } else {
            $this->validate();
        }

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
        ];

        BlogCategory::updateOrCreate(['id' => $this->categoryId], $data);

        session()->flash('message', $this->categoryId ? 'Categoria atualizada!' : 'Categoria criada com sucesso!');

        $this->toggleForm();
    }

    public function edit($id)
    {
        $category = BlogCategory::findOrFail($id);
        $this->categoryId = $id;
        $this->name = $category->name;
        $this->showForm = true;
    }

    public function delete($id)
    {
        $category = BlogCategory::findOrFail($id);

        // Verifica se existem posts usando esta categoria para evitar quebrar o banco
        if ($category->posts()->count() > 0) {
            session()->flash('error', 'Não é possível excluir! Esta categoria possui notícias vinculadas.');
            return;
        }

        $category->delete();
        session()->flash('message', 'Categoria removida.');
    }
}
