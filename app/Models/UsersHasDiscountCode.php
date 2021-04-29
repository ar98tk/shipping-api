<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersHasDiscountCode extends Model
{
    use HasFactory;

    protected $table = 'users_has_discount_code';
    public $timestamps = false;
    protected $fillable = [
        'users_id',
        'discount_code_id'
    ];
}
