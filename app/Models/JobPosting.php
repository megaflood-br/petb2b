<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'title',
        'slug',
        'description',
        'type',
        'city',
        'state',
        'salary',
        'how_to_apply',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Tipos de contratação disponíveis. */
    public const TYPES = ['CLT', 'PJ', 'Estágio', 'Freelancer', 'Temporário'];

    protected static function booted(): void
    {
        static::creating(function (JobPosting $job) {
            if (empty($job->slug)) {
                $job->slug = Str::slug($job->title) . '-' . Str::lower(Str::random(6));
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
