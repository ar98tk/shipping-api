<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $table = 'bills';
    public $timestamps = ['created_at'];
    const UPDATED_AT   = null;

    protected $fillable = [
      'orders_id',
      'cost',
        'discount',
        'payment_type',
        'status',
        'fees'
    ];
    public function orders() {
        return $this->belongsTo(Order::class);
    }
}
