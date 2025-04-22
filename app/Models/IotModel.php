<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IotModel extends Model
{
    /** @use HasFactory<\Database\Factories\IotModelFactory> */
    use HasFactory,  SoftDeletes;

    protected $table = 'Iot_waterlevel_devices';

    protected $fillable = [
        'device_id',
        'device_name',
        'latitude',
        'longitude',
        'status' 
    ];
}
