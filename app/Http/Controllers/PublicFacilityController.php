<?php

namespace App\Http\Controllers;

// TODO coba implementasiin redis untuk caching
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicFacilityRequest;
use App\Models\PublicFacility;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;

class PublicFacilityController extends Controller
{
    /**
     * Menampilkan daftar fasilitas publik dengan paginasi dan pencarian.
     *
     * @param Request $request
     * @return ApiResponse
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'nama_fasilitas',
            'status_operasional',
            'alamat',
            'id_kategori'
        ]);

        $perPage = $request->input('perPage', 10); // Default 10

        $fasilitas = PublicFacility::with('kategori')
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->appends($request->query()); // Pertahankan parameter di pagination

        return new ApiResponse(true, 'Data Fasilitas Publik Berhasil Ditemukan!', $fasilitas);
    }


    public function store(StorePublicFacilityRequest $request)
    {
        // dd($request);
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
     *
     * @param int $id
     * @return ApiResponse
     */
    public function show($id)
    {
        $publicFacility = PublicFacility::with('kategori')->findOrFail($id);
        return new ApiResponse(true, 'Data Fasilitas Publik Ditemukan!', $publicFacility);
    }

    /**
     * Memperbarui data fasilitas publik.
     *
     * @param Request $request
     * @param int $id
     * @return ApiResponse
     */
    public function update(Request $request, $id)
    {
        $publicFacility = PublicFacility::findOrFail($id);

        $validated = $request->validate([
            'id_kategori' => 'sometimes|required|exists:kategori_fasilitas,id',
            'nama_fasilitas' => 'sometimes|required|string|max:255',
            'alamat' => 'sometimes|required|string',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'deskripsi' => 'nullable|string',
            'status_operasional' => 'sometimes|required|string|max:50',
        ]);

        $publicFacility->update($validated);

        return new ApiResponse(true, 'Data Fasilitas Publik Berhasil Diperbarui!', $publicFacility->load('kategori'));
    }

    /**
     * Menghapus data fasilitas publik.
     *
     * @param PublicFacility $publicFacility
     * @return ApiResponse
     */
    public function destroy(PublicFacility $publicFacility)
    {
        $publicFacility->delete();
        return new ApiResponse(true, 'Data Fasilitas Publik Berhasil Dihapus!', null);
    }

    /**
     * Menampilkan statistik fasilitas publik (untuk summary card).
     *
     * @return ApiResponse
     */
    public function stats()
    {
        $stats = PublicFacility::selectRaw('status_operasional, count(*) as count')
            ->groupBy('status_operasional')
            ->pluck('count', 'status_operasional');

        return new ApiResponse(true, 'Statistik Fasilitas Publik Ditemukan!', [
            'total' => PublicFacility::count(),
            'beroperasi' => $stats->get('Beroperasi', 0),
            'tutup' => $stats->get('Tutup', 0),
            'maintenance' => $stats->get('Maintenance', 0),
        ]);
    }
}