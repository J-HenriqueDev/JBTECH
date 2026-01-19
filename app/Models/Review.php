<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_review_id',
        'author_name',
        'profile_photo',
        'rating',
        'text',
        'time',
    ];

    protected $casts = [
        'time' => 'datetime',
    ];
}
