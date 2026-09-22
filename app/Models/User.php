<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cnpj',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string $role): bool
    {
        return strtolower(trim((string) $this->role)) === strtolower($role);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSupplier(): bool
    {
        return $this->hasRole('supplier');
    }

    public function isBreeder(): bool
    {
        return $this->hasRole('breeder');
    }

    /** Rota do painel após login / clique em Dashboard. */
    public function panelRouteName(): string
    {
        return match (true) {
            $this->isAdmin() => 'admin.dashboard',
            $this->isSupplier() => 'supplier.dashboard',
            $this->isBreeder() => 'breeder.dashboard',
            default => 'profile',
        };
    }
}
