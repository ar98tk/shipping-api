<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public $timestamps = ['created_at'];
    const UPDATED_AT   = null;

    public function bill(){
        return $this->hasOne(Bill::class);
    }

    protected $fillable = [
        'country_code',
        'image',
        'goods_types_id',
        'trucks_types_id',
        'load_weight',
        'descriptions',
        'locations_pickup_id',
        'locations_destination_id',
        'phone',
        'recipient_name',
        'i_am_recipient',
    ];
}
