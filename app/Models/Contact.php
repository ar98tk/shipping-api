<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    public $timestamps = ['created_at'];
    const UPDATED_AT   = null;
    protected $fillable = [
        'message',
        'users_id',
        'drivers_id',
        'code',
        'status',
        'contacts_types_id'
    ];


}
