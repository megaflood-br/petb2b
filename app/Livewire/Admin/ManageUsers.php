<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ManageUsers extends Component
{
    use WithPagination;
    use WithInfiniteScroll;

    public string $search = '';

    public string $roleFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $editRole = User::ROLE_READER;

    protected function infiniteIncrement(): int
    {
        return 15;
    }

    public function updatingSearch(): void
    {
        $this->resetInfiniteScroll();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetInfiniteScroll();
    }

    public function setRoleFilter(string $role): void
    {
        $this->roleFilter = $role;
        $this->resetInfiniteScroll();
    }

    public function toggleForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password']);
        $this->editRole = User::ROLE_READER;
        $this->resetErrorBag();
        $this->showForm = ! $this->showForm;
    }

    public function edit(int $id): void
    {
        $user = $this->findManageable($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->editRole = array_key_exists($user->role, User::MANAGEABLE_ROLES)
            ? $user->role
            : User::ROLE_READER;
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|min:2|max:120',
            'email' => 'required|email|max:150|unique:users,email,'.($this->editingId ?? 'NULL'),
            'editRole' => 'required|in:'.implode(',', array_keys(User::MANAGEABLE_ROLES)),
            'password' => $this->editingId ? 'nullable|min:8' : 'required|min:8',
        ];

        $this->validate($rules);

        if ($this->editingId) {
            $user = $this->findManageable($this->editingId);
            $user->fill([
                'name' => $this->name,
                'email' => $this->email,
            ]);
            if ($this->password !== '') {
                $user->password = Hash::make($this->password);
            }
            $user->save();
            $user->forceFill(['role' => $this->editRole])->save();
            session()->flash('message', 'Usuário atualizado.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
            ]);
            if ($this->editRole !== User::ROLE_READER) {
                $user->forceFill(['role' => $this->editRole])->save();
            }
            session()->flash('message', 'Usuário cadastrado.');
        }

        $this->toggleForm();
    }

    public function changeRole(int $id, string $role): void
    {
        if (! array_key_exists($role, User::MANAGEABLE_ROLES)) {
            session()->flash('error', 'Perfil inválido.');

            return;
        }

        $user = $this->findManageable($id);
        $user->forceFill(['role' => $role])->save();
        session()->flash('message', 'Perfil de '.$user->name.' atualizado para '.$user->roleLabel().'.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $users = User::query()
            ->with(['supplier', 'kennel'])
            ->where('role', '!=', User::ROLE_ADMIN)
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter !== '' && array_key_exists($this->roleFilter, User::MANAGEABLE_ROLES), function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->latest()
            ->paginate($this->perPage);

        $counts = User::query()
            ->where('role', '!=', User::ROLE_ADMIN)
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('livewire.admin.manage-users', [
            'users' => $users,
            'counts' => $counts,
            'roles' => User::MANAGEABLE_ROLES,
        ]);
    }

    private function findManageable(int $id): User
    {
        $user = User::findOrFail($id);

        if ($user->role === User::ROLE_ADMIN) {
            abort(403, 'Administradores não são gerenciados nesta tela.');
        }

        if ($user->id === auth()->id()) {
            abort(403, 'Você não pode editar a própria conta por aqui.');
        }

        return $user;
    }
}
