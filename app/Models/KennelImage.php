<?php

namespace App\Models;

// Corrigido: Adicionado o Eloquent no namespace do import
use Illuminate\Database\Eloquent\Model;

class KennelImage extends Model
{
    protected $fillable = ['kennel_id', 'image_path'];

    /**
     * Relacionamento: A foto pertence a um Canil
     */
    public function kennel()
    {
        return $this->belongsTo(Kennel::class);
    }
}
