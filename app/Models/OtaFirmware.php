<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtaFirmware extends Model
{
    protected $fillable = ['filename', 'version', 'is_latest'];
}
