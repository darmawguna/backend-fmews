<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IotModel extends Model
{
    /** @use HasFactory<\Database\Factories\IotModelFactory> */
    use HasFactory,  SoftDeletes;

    protected $table = 'Iot_devices';

    protected $fillable = [
        'device_id',
        'device_name',
        'latitude',
        'longitude',
        'status',
        'location',
        'warning_level',
        'danger_level',
        'sensor_height',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    public function iotDeviceToken()
    {
        return $this->hasMany(IotDeviceToken::class, 'device_id', 'device_id');
    }

    public function scopeFilter(Builder $query): void
    {
        if (request()->has('search')) {
            $search = request()->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('device_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if (request()->has('device_name')) {
            $query->where('device_name', 'like', '%' . request()->device_name . '%');
        }

        if (request()->has('status')) {
            $query->where('status', request()->status);
        }

        if (request()->has('location')) {
            $query->where('location', 'like', '%' . request()->location . '%');
        }

        if (request()->has('warning_level')) {
            $query->where('warning_level', request()->warning_level);
        }

        if (request()->has('danger_level')) {
            $query->where('danger_level', request()->danger_level);
        }
    }
}
