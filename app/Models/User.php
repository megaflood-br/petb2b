<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\FavoriteCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        'avatar_path',
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

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function roleLabel(): string
    {
        return self::MANAGEABLE_ROLES[$this->role] ?? 'Admin';
    }

    public function hasPanel(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPPLIER, self::ROLE_BREEDER], true);
    }

    public function avatarUrl(): ?string
    {
        if (! filled($this->avatar_path)) {
            return null;
        }

        $path = ltrim((string) $this->avatar_path, '/');
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return (string) $this->avatar_path;
        }

        return asset('storage/'.$path);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $first = Str::substr($parts[0] ?? 'U', 0, 1);
        $last = count($parts) > 1 ? Str::substr((string) end($parts), 0, 1) : '';

        return Str::upper($first.$last);
    }

    public function hasFavorited(Model $model): bool
    {
        if (! FavoriteCatalog::supports($model)) {
            return false;
        }

        return $this->favorites()
            ->where('favoritable_type', $model->getMorphClass())
            ->where('favoritable_id', $model->getKey())
            ->exists();
    }

    public function toggleFavorite(Model $model): bool
    {
        if (! FavoriteCatalog::supports($model)) {
            return false;
        }

        $deleted = $this->favorites()
            ->where('favoritable_type', $model->getMorphClass())
            ->where('favoritable_id', $model->getKey())
            ->delete();

        if ($deleted) {
            return false;
        }

        $this->favorites()->create([
            'favoritable_type' => $model->getMorphClass(),
            'favoritable_id' => $model->getKey(),
            'folder' => FavoriteCatalog::folder($model),
        ]);

        return true;
    }

    public function replaceAvatar(string $path): void
    {
        $previous = $this->avatar_path;
        $this->forceFill(['avatar_path' => $path])->save();

        if (filled($previous) && $previous !== $path && ! str_starts_with((string) $previous, 'http')) {
            Storage::disk('public')->delete($previous);
        }
    }
}
