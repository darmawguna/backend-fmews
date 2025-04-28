<?php

namespace App\Http\Controllers\IotController;

use App\Http\Controllers\Controller;
use App\Http\Resources\IoTWaterlevelResources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\IotModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class IotCommandController extends Controller
{
    public function index()
    {
        //
    }

    public function setThreshold(Request $request, $device_id)
    {
        $device = IotModel::where('device_id', $device_id)
            ->where('status', 'active')
            ->first();

        // dd($device);    
        if (!$device) {
            return new IoTWaterlevelResources(false, 'IoT Device Not Found!', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'warning_level' => 'required|numeric',
            'danger_level' => 'required|numeric',
            'sensor_height' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $payload = [
            'sensor_id' => $request->sensor_id,
            'warning_level' => $request->warning_level,
            'danger_level' => $request->danger_level,
            'sensor_height' => $request->sensor_height,
        ];

        $iotServerUrl = env('IOT_SERVER_URL') . '/api/iot/threshold';

        try {
            $response = Http::timeout(5)->post($iotServerUrl, $payload);

            if ($response->successful()) {
                return new IoTWaterlevelResources(true, 'Threshold command sent successfully!', $payload);
            }

            $device->update(
                [
                    "warning_level" => $request->warning_level,
                    "danger_level" => $request->danger_level,
                    "sensor_height" => $request->sensor_height,
                ]
            );

            $device->save();

            return new IoTWaterlevelResources(false, 'Failed to send command to IoT Server', null, 404);

        } catch (\Exception $e) {
            Log::error('IoT Server Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to connect to IoT Server',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function changeStatus($device_id, Request $request)
    {
        $device = IotModel::where('device_id', $device_id)->first();

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

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
