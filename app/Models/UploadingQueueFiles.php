<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadingQueueFiles extends Model
{
    public $fillable = [
        'type',
        'file_name',
        'mime_type',
        'file_id',
        'file_unique_id',
        'file_size',
        'caption',
        'uploading_queues_id'
    ];
}
