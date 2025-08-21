<?php

namespace App\Http\Controllers;
// TODO buat unit test dan feature test untuk controller ini
use App\Http\Controllers\Controller;
use App\Models\Shelter;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
class ShelterController extends Controller
{
    /**
     * Menampilkan daftar shelter dengan paginasi.
     */
    public function index()
    {
        $perPage = request()->input('perPage', 10);
        $shelters = Shelter::filter()->paginate($perPage);
        return new ApiResponse(true, 'Data Shelter Berhasil Ditemukan!', $shelters);
    }

    /**
     * Menampilkan semua data shelter tanpa paginasi.
     *
     * @return ApiResponse
     */
    public function getAll() 
    {
        $shelters = Shelter::all();
        return new ApiResponse(true, 'Data Shelter Berhasil Ditemukan!', $shelters);
    }

    /**
     * Menyimpan data shelter baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_shelter' => 'required|string|max:255',
            'alamat' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'kapasitas_maksimum' => 'required|integer|min:0',
            'ketersediaan_saat_ini' => 'sometimes|integer|min:0',
            'status' => ['required', Rule::in(['Buka', 'Penuh', 'Tutup'])],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $shelter = Shelter::create($validator->validated());

        return new ApiResponse(true, 'Data Shelter Berhasil Ditambahkan!', $shelter);
    }

    /**
     * Menampilkan detail satu shelter.
     */
    public function show(Shelter $shelter)
    {
        return new ApiResponse(true, 'Data Shelter Ditemukan!', $shelter);
    }

    /**
     * Memperbarui data shelter.
     */
    public function update(Request $request, $id)
    {
        $shelter = Shelter::findOrFail($id);

        // Cek apakah shelter ditemukan
        if (!$shelter) {
            return response()->json(['error' => 'Data Shelter Tidak Ditemukan!'], 404);
        }

        // dd($request->all());

        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_shelter' => 'sometimes|required|string|max:255',
            'alamat' => 'sometimes|required|string',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'kapasitas_maksimum' => 'sometimes|required|integer|min:0',
            'ketersediaan_saat_ini' => 'sometimes|integer|min:0',
            'status' => ['sometimes', 'required', Rule::in(['Buka', 'Penuh', 'Tutup'])],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Pastikan ketersediaan tidak melebihi kapasitas
        if ($request->has('ketersediaan_saat_ini') && $request->ketersediaan_saat_ini > $shelter->kapasitas_maksimum) {
            return response()->json(['ketersediaan_saat_ini' => ['Ketersediaan saat ini tidak boleh melebihi kapasitas maksimum.']], 422);
        }

        $shelter->update($validator->validated());

        return new ApiResponse(true, 'Data Shelter Berhasil Diperbarui!', $shelter);
    }

    public function getShelterStats()
    {
        try {
            $totalShelter = Shelter::count();
            dd($totalShelter);

            // Hitung perangkat berdasarkan status 'active' atau 'deactive'
            $activeShelter = Shelter::where('status', 'Buka')->count();
            $closedShelter = Shelter::where('status', 'Tutup')->count();
            $fulledShelter = Shelter::where('status', 'Penuh')->count();

            $stats = [
                'totalShelter' => $totalShelter,
                'activeShelter' => $activeShelter,
                'closedShelter' => $closedShelter,
                'fulledShelter' => $fulledShelter,
            ];
            return new ApiResponse(true, 'Statistik Shelter Berhasil Ditemukan!', $stats);

        } catch (\Exception $e) {
            Log::error('Failed to fetch dashboard stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve dashboard statistics.'
            ], 500);
        }
    }

    /**
     * Menghapus data shelter.
     */
    public function destroy(Shelter $shelter)
    {
        $shelter->delete();
        return new ApiResponse(true, 'Data Shelter Berhasil Dihapus!', null);
    }
}
