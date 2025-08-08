<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadingQueue extends Model
{
    public $fillable = [
        'parent_folder_id',
        'user_id'
    ];
}
