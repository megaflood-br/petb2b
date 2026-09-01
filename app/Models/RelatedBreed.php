<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatedBreed extends Model
{
    protected $table = 'breed_kennel';
    protected $fillable = ['kennel_id', 'breed_name'];

    public function kennel()
    {
        return $this->belongsTo(Kennel::class);
    }
}
