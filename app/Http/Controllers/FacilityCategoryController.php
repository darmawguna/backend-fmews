<?php

namespace App\Http\Controllers;

use App\Models\FacilityCategory;
use App\Http\Requests\StoreFacilityCategoryRequest;
use App\Http\Requests\UpdateFacilityCategoryRequest;
use App\Http\Resources\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class FacilityCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategori = FacilityCategory::latest()->paginate(10);
        return new ApiResponse(true, 'Data Kategori Fasilitas Berhasil Ditemukan!', $kategori);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:100|unique:kategori_fasilitas,nama_kategori',
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kategori = FacilityCategory::create($validator->validated());

        return new ApiResponse(true, 'Data Kategori Fasilitas Berhasil Ditambahkan!', $kategori);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $facilityCategory = FacilityCategory::findOrFail($id);
        if (!$facilityCategory) {
            return response()->json(['error' => 'Data Kategori Fasilitas Tidak Ditemukan!'], 404);
        }
        return new ApiResponse(true, 'Data Kategori Fasilitas Ditemukan!', $facilityCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $facilityCategory = FacilityCategory::findOrFail($id);
        if (!$facilityCategory) {
            return response()->json(['error' => 'Data Kategori Fasilitas Tidak Ditemukan!'], 404);
        }
        $validator = Validator::make($request->all(), [
            'nama_kategori' => "required|string|max:100|unique:kategori_fasilitas,nama_kategori,{$facilityCategory->id}",
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $facilityCategory->update($validator->validated());

        return new ApiResponse(true, 'Data Kategori Fasilitas Berhasil Diperbarui!', $facilityCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $facilityCategory = FacilityCategory::findOrFail($id);
        if (!$facilityCategory) {
            return response()->json(['error' => 'Data Kategori Fasilitas Tidak Ditemukan!'], 404);
        }

        // Pastikan tidak ada fasilitas publik yang terkait dengan kategori ini
        if ($facilityCategory->publicFacilities()->count() > 0) {
            return response()->json(['error' => 'Kategori Fasilitas Tidak Dapat Dihapus Karena Masih Ada Fasilitas Publik Terkait!'], 422);
        }       
        $facilityCategory->delete();
        return new ApiResponse(true, 'Data Kategori Fasilitas Berhasil Dihapus!', null);
    }
}
