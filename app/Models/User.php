<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_READER = 'reader';
    public const ROLE_SUPPLIER = 'supplier';
    public const ROLE_BREEDER = 'breeder';
    public const ROLE_ADMIN = 'admin';

    /** Papéis que o cadastro público e o painel podem atribuir. */
    public const MANAGEABLE_ROLES = [
        self::ROLE_READER => 'Lojista',
        self::ROLE_SUPPLIER => 'Fornecedor',
        self::ROLE_BREEDER => 'Canil',
    ];

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

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class);
    }

    public function kennel(): HasOne
    {
        return $this->hasOne(Kennel::class);
    }

    public function roleLabel(): string
    {
        return self::MANAGEABLE_ROLES[$this->role] ?? 'Admin';
    }
}
