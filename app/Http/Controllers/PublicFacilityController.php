<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicFacilityRequest;
use App\Models\PublicFacility;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PublicFacilityController extends Controller
{
    /**
     * Menampilkan daftar fasilitas publik dengan paginasi.
     */
    public function index()
    {
        // Eager loading relasi 'kategori' untuk efisiensi query
        $fasilitas = PublicFacility::with('kategori')->latest()->paginate(10);
        return new ApiResponse(true, 'Data Fasilitas Publik Berhasil Ditemukan!', $fasilitas);
    }

    /**
     * Menyimpan fasilitas publik baru.
     */
    public function store(StorePublicFacilityRequest $request)
    {
        try {
            $validated = $request->validated();
            $fasilitas = PublicFacility::create($validated);
            return new ApiResponse(true, 'Data Fasilitas Publik Berhasil Ditambahkan!', $fasilitas->load('kategori'));
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Data Fasilitas Publik Gagal Ditambahkan!'], 500);
        }
    }

    /**
     * Menampilkan detail satu fasilitas publik.
     */
    public function show($id)
    {
        $publicFacility = PublicFacility::with('kategori')->findOrFail($id);
        // Cek apakah fasilitas publik ditemukan
        if (!$publicFacility) {
            return response()->json(['error' => 'Data Fasilitas Publik Tidak Ditemukan!'], 404);
        }
        // Muat relasi kategori saat menampilkan detail
        return new ApiResponse(true, 'Data Fasilitas Publik Ditemukan!', $publicFacility->load('kategori'));
    }

    /**
     * Memperbarui data fasilitas publik.
     */
    public function update(Request $request, $id)
    {
        $publicFacility = PublicFacility::findOrFail($id);
        // Cek apakah fasilitas publik ditemukan
        if (!$publicFacility) {
            return response()->json(['error' => 'Data Fasilitas Publik Tidak Ditemukan!'], 404);
        }
        $validator = Validator::make($request->all(), [
            'id_kategori' => 'sometimes|required|exists:kategori_fasilitas,id',
            'nama_fasilitas' => 'sometimes|required|string|max:255',
            'alamat' => 'sometimes|required|string',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'deskripsi' => 'nullable|string',
            'status_operasional' => 'sometimes|required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $publicFacility->update($validator->validated());

        return new ApiResponse(true, 'Data Fasilitas Publik Berhasil Diperbarui!', $publicFacility->load('kategori'));
    }

    /**
     * Menghapus data fasilitas publik.
     */
    public function destroy(PublicFacility $publicFacility)
    {
        $publicFacility->delete();
        return new ApiResponse(true, 'Data Fasilitas Publik Berhasil Dihapus!', null);
    }
}
