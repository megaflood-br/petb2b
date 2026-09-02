<?php

namespace App\Models;

use App\Models\Concerns\FlushesHomeCache;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
