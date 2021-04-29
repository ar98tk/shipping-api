<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Driver extends Authenticatable implements JWTSubject
{
    use HasFactory;
    public $timestamps = ['created_at'];
    const UPDATED_AT   = null;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    protected $fillable = [
        'image',
        'name',
        'country_code',
        'phone',
        'car_name',
        'car_model',
        'password',
        'language',
        'car_photo',
        'car_license_number',
        'driving_license_image',
        'car_license_image',
        'language',
        'trucks_types_id',
        'id_image',
        'api_token'
    ];
}
