<?php

namespace App\Models;

use App\Models\Concerns\FlushesHomeCache;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use FlushesHomeCache;

    protected $fillable = [
        'title', 'slug', 'category', 'rating', 'pros', 'cons', 'content', 'verdict', 'image', 'is_active'
    ];
}
