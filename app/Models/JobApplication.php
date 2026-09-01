<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_posting_id',
        'name',
        'email',
        'phone',
        'message',
        'resume_path',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }
}
