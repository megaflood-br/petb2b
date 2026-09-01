<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    /**
     * Campos que podem ser preenchidos em massa.
     * Isso resolve o erro MassAssignmentException.
     */
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read', // Adicionamos este também para a gestão no admin
    ];
}
