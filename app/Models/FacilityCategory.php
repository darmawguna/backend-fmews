<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityCategory extends Model
{
    /** @use HasFactory<\Database\Factories\FacilityCategoryFactory> */
    use HasFactory;

    protected $table = 'kategori_fasilitas'; // Menegaskan nama tabel

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    // Relasi: Satu Kategori punya banyak Fasilitas
    public function fasilitasPublik()
    {
        return $this->hasMany(PublicFacility::class, 'id_kategori');
    }

}
