<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramFolder extends Model
{
    /** @use HasFactory<\Database\Factories\TelegramFolderFactory> */
    use HasFactory;
    public $fillable = [
        "user_id",
        "parent_folder_id",
        "name",
    ];
}
