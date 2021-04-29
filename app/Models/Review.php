<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $table = 'reviews';
    public $timestamps = ['created_at'];
    const UPDATED_AT   = null;
    protected $fillable = [
        'rate',
        'users_id',
        'drivers_id',
        'type',
        'orders_id',
    ];
}
