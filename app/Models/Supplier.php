<?php

namespace App\Models;

use App\Http\Controllers\HomeController;
use App\Models\Concerns\FlushesHomeCache;
use App\Models\Concerns\Searchable;
use App\Support\CpfCnpj;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory, Searchable, FlushesHomeCache;

   protected $fillable = [
    'user_id', // Adicione esta linha
    'name',
    'slug',
    'category',
    'description',
    'email',
    'cnpj',
    'city',
    'address',
    'state',
    'seo_title',       // LIBERADO PARA ENTRADA AUTOMÁTICA
    'seo_description', // LIBERADO PARA ENTRADA AUTOMÁTICA
    'phone',
    'whatsapp',
    'website',
    'logo',
    'is_active',
    'is_verified',
    'is_approved',
];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
        'credit_balance' => 'decimal:4',
    ];

    // Otimização de SEO: Gera o slug automaticamente ao salvar o nome
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->slug)) {
                $supplier->slug = Str::slug($supplier->name);
            }
        });

        // Mantém category_id em sincronia com o slug de categoria (texto),
        // garantindo integridade referencial sem quebrar os filtros por slug.
        static::saving(function ($supplier) {
            if ($supplier->isDirty('category') || is_null($supplier->category_id)) {
                if (! empty($supplier->category)) {
                    $category = Category::firstOrCreate(
                        ['slug' => $supplier->category],
                        ['name' => Str::title(str_replace('-', ' ', $supplier->category))]
                    );
                    $supplier->category_id = $category->id;
                } else {
                    $supplier->category_id = null;
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * CPF/CNPJ aceito pelo Asaas: o da empresa ou, se faltar, o do dono da conta.
     */
    public function pixDocument(): ?string
    {
        $this->loadMissing('user');

        return CpfCnpj::normalize($this->cnpj)
            ?? CpfCnpj::normalize($this->user?->cnpj);
    }

    /**
     * Categoria normalizada (FK). O atributo texto `category` (slug) é mantido
     * para compatibilidade; este relacionamento dá integridade e eager loading.
     */
    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function advertisements()
    {
        return $this->hasMany(Advertisement::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(SupplierCreditTransaction::class);
    }

    public function pixCharges()
    {
        return $this->hasMany(PixCharge::class);
    }

    public function classifieds()
    {
        return $this->hasMany(Classified::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }

    /**
     * Selo azul do guia: só empresas com os dados públicos preenchidos de verdade.
     * Placeholders da importação e e-mails temporários não contam.
     */
    public function hasCompleteProfile(): bool
    {
        $email = trim((string) $this->email);

        return $this->isFilledValue($this->name)
            && $this->isFilledValue($this->category)
            && $this->isFilledValue($this->description)
            && mb_strlen(trim((string) $this->description)) >= 10
            && $this->isFilledValue($this->address)
            && $this->isFilledValue($this->city)
            && $this->isFilledValue($this->state)
            && $this->isFilledValue($this->logo)
            && $email !== ''
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && ! str_contains(mb_strtolower($email), '@temporario-')
            && ($this->isFilledValue($this->phone) || $this->isFilledValue($this->whatsapp));
    }

    protected function isFilledValue(?string $value): bool
    {
        $value = trim((string) $value);

        if ($value === '') {
            return false;
        }

        return ! in_array(mb_strtolower($value), [
            'empresa ainda não preencheu esta informação.',
            'preencha a descrição da sua empresa aqui.',
            'nenhuma descrição complementar cadastrada para este fornecedor.',
            'nenhuma descrição detalhada disponível.',
            'endereço a completar',
            'preencha o seu endereço comercial.',
        ], true);
    }

    /**
     * Apaga todas as empresas do guia e o conteúdo comercial ligado a elas.
     * Contas de usuário, canis, categorias e matérias editoriais permanecem.
     * Matérias patrocinadas só perdem o vínculo com a empresa.
     */
    public static function wipeDirectory(): int
    {
        $count = static::query()->count();

        DB::transaction(function () {
            Post::query()->whereNotNull('supplier_id')->update(['supplier_id' => null]);

            SupplierCreditTransaction::query()->delete();
            Advertisement::query()->whereNotNull('supplier_id')->delete();
            PixCharge::query()->delete();
            CompanyClaim::query()->delete();
            Lead::query()->delete();
            Classified::query()->delete();
            JobApplication::query()->delete();
            JobPosting::query()->delete();
            static::query()->delete();
        });

        Cache::forget(HomeController::CACHE_KEY);

        return $count;
    }
}
