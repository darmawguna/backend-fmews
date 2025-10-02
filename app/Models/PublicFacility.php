<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicFacility extends Model
{
    use HasFactory, SoftDeletes;

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

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relasi: Satu Fasilitas dimiliki oleh satu Kategori
    public function kategori()
    {
        return $this->belongsTo(FacilityCategory::class, 'id_kategori');
    }

    /**
     * Scope untuk filter data fasilitas berdasarkan berbagai kriteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters [search, nama_fasilitas, status_operasional, alamat, id_kategori]
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where('nama_fasilitas', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%")
                ->orWhereHas('kategori', function ($q) use ($search) {
                    $q->where('nama_kategori', 'like', "%{$search}%");
                });
        })
            ->when($filters['nama_fasilitas'] ?? null, function ($q, $name) {
                $q->where('nama_fasilitas', 'like', "%{$name}%");
            })
            ->when($filters['status_operasional'] ?? null, function ($q, $status) {
                $q->where('status_operasional', $status);
            })
            ->when($filters['alamat'] ?? null, function ($q, $alamat) {
                $q->where('alamat', 'like', "%{$alamat}%");
            })
            ->when($filters['id_kategori'] ?? null, function ($q, $kategoriId) {
                $q->where('id_kategori', $kategoriId);
            });
    }
}