<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{

    use HasFactory;
    protected $table = 'discount_code';
    protected $primaryKey = 'code';
    public $timestamps = ['created_at'];
    const UPDATED_AT = null;

}
