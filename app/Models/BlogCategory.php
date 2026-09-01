<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
   protected $fillable = ['name', 'slug'];

// Relacionamento: Uma categoria tem muitos posts
public function posts()
{
    return $this->hasMany(Post::class, 'blog_category_id');
}
}
