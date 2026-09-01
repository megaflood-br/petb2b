<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classified extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'title', 'slug', 'description', 'price', 'image', 'condition', 'is_active'];

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
}
