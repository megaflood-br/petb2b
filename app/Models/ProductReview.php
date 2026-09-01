<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [
        'title', 'slug', 'category', 'rating', 'pros', 'cons', 'content', 'verdict', 'image', 'is_active'
    ];
}
