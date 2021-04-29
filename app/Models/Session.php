<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    public $timestamps = ['created_at'];
    const UPDATED_AT   = null;
    protected $table = 'sessions';

    protected $fillable = [
        'code',
        'users_id',
        'drivers_id',
        'tmp_phone',
        'tmp_code',
    ];
}
