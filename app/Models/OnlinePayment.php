<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlinePayment extends Model
{
    use HasFactory;
    protected $table = 'online_payment';
    public $timestamps = ['created_at'];
    const UPDATED_AT   = null;
    protected $fillable = [
        'bills_id',
        'transaction_id',
        'payment_status',
        'payment_date'
    ];
}
