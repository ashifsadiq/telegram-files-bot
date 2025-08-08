<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentQueue extends Model
{
    /** @use HasFactory<\Database\Factories\CurrentQueueFactory> */
    use HasFactory;
    public $fillable = [
        'user_id',
        'name'
    ];
}
