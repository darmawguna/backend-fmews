<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Shelter extends Model
{
    /** @use HasFactory<\Database\Factories\ShelterFactory> */
    use HasFactory;
    protected $table = 'shelters';

    protected $fillable = [
        'nama_shelter',
        'alamat',
        'latitude',
        'longitude',
        'kapasitas_maksimum',
        'ketersediaan_saat_ini',
        'status',
    ];

    public function scopeFilter(Builder $query): void
    {

        if (request()->has('nama_shelter')) {
            $query->where('nama_shelter', 'like', '%' . request()->nama_shelter . '%');
        }
    }
}
