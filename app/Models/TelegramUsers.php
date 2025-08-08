<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUsers extends Model
{
    /** @use HasFactory<\Database\Factories\TelegramUsersFactory> */
    use HasFactory;
    protected $primaryKey = 'user_id';
    public $incrementing  = false; // important: string PKs are not auto-incrementing
    protected $keyType    = 'string';

    protected $fillable = ['user_id', 'first_name', 'last_name', 'username'];
}
