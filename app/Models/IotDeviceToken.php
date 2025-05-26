<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
class IotDeviceToken extends Model
{
    protected $table = 'iot_device_tokens';

    protected $fillable = [
        'device_id',
        'device_token',
        'status',
        'expired_at',
        'used_at',
    ];

    protected $dates = ['used_at', 'expired_at'];

    public function iotModel()
    {
        return $this->belongsTo(IotModel::class, 'device_id', 'device_id');
    }

    public function isValid()
    {
        return !$this->used_at && (!$this->expired_at || $this->expired_at->isFuture());
    }
}
