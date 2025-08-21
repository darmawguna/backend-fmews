<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicFacility extends Model
{
    /** @use HasFactory<\Database\Factories\PublicFacilityFactory> */
    use HasFactory;

    protected $table = 'fasilitas_publik';

    protected $fillable = [
        'id_kategori',
        'nama_fasilitas',
        'alamat',
        'latitude',
        'longitude',
        'deskripsi',
        'status_operasional',
    ];

    // Relasi: Satu Fasilitas dimiliki oleh satu Kategori
    public function kategori()
    {
        return $this->belongsTo(FacilityCategory::class, 'id_kategori');
    }

}
