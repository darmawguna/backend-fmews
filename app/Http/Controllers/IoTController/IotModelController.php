<?php

namespace App\Http\Controllers\IotController;

use App\Http\Controllers\Controller;
use App\Models\IotModel;
use App\Http\Requests\StoreIotModelRequest;
use App\Http\Requests\UpdateIotModelRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Http\Resources\IoTWaterlevelResources;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IotModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $iotData = IotModel::all();
        return new IoTWaterlevelResources(true, 'Data IoT Berhasil Ditemukan!', $iotData);
    }

    public function getAll()
    {
        $iotData = IotModel::paginate(15);
        return new IoTWaterlevelResources(true, 'Data IoT Berhasil Ditemukan!', $iotData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'status' => 'required|in:active,deactive',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $iotDevice = IotModel::create([
            'device_id' => Str::uuid(), // auto-generate UUID
            'device_name' => $request->device_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => $request->status,
        ]);

        return new IoTWaterlevelResources(true, 'Data IoT Berhasil Ditambahkan!', $iotDevice);
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // dd($id);
        $device = IotModel::where('device_id', $id)
            ->first();
        
        if (!$device) {
            return new IoTWaterlevelResources(false, 'Iot Not Found!', null,404);
        }
        // return response()->json(['message' => 'Device is authorized']);
        return new IoTWaterlevelResources(true, 'Iot Data is Found!', $device);
    }

    public function verify($id)
    {
        $device = IotModel::where('device_id', $id)
            ->first();

        if (!$device) {
            return new IoTWaterlevelResources(false, 'Unathorized!', null, 401);
        }
        // return response()->json(['message' => 'Device is authorized']);
        return new IoTWaterlevelResources(true, 'Device is authorized!', null
    );
    }

    public function changeStatus($id, Request $request)
    {
        $device = IotModel::where('device_id', $id)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', Rule::in(['active', 'deactive'])],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $device->status = $request->input('status'); // sudah dijamin valid & string
        $device->save();

        return new IoTWaterlevelResources(
            true,
            'Update Status successfully!',
            $device
        );
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIotModelRequest $request, IotModel $iotModel)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $device = IotModel::where('device_id', $id)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found.',
            ], 404);
        }

        // $iotModel->delete();
        $device->delete();
        return new IoTWaterlevelResources(
            true,
            'Device soft deleted successfully!',
            null
        );
    }
}
