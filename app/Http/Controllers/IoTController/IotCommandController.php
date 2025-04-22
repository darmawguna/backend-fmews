<?php

namespace App\Http\Controllers\IotController;

use App\Http\Controllers\Controller;
use App\Http\Resources\IoTWaterlevelResources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\IotModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

        // $validated = $request->validate([
        //     'warning_level' => 'required|numeric',
        //     'danger_level' => 'required|numeric',
        //     'sensor_height' => 'required|numeric'
        // ]);

        $validator = Validator::make($request->all(), [
            'warning_level' => 'required|numeric',
            'danger_level' => 'required|numeric',
            'sensor_height' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // dd($validated);

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

             return new IoTWaterlevelResources(false, 'Failed to send command to IoT Server', null, 404);

            // return response()->json([
            //     'success' => false,
            //     'message' => 'Failed to send command to IoT Server',
            //     'error' => $response->body()
            // ], $response->status());

        } catch (\Exception $e) {
            Log::error('IoT Server Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to connect to IoT Server',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function setSensorHeight(Request $request, $device_id)
    {
        $device = IotModel::where('device_id', $device_id)
            ->where('status', 'active')
            ->first();

        if (!$device) {
            return new IoTWaterlevelResources(false, 'IoT Device Not Found!', null, 404);
        }

        $validated = $request->validate([
            'sensor_height' => 'required|numeric|min:0'
        ]);

        $payload = [
            'device_id' => $device_id,
            'command' => 'set_sensor_height',
            'sensor_height' => $validated['sensor_height'],
        ];

        $iotServerUrl = env('IOT_SERVER_URL') . '/api/push-command';

        try {
            $response = Http::timeout(5)->post($iotServerUrl, $payload);

            if ($response->successful()) {
                return new IoTWaterlevelResources(true, 'Sensor height updated successfully!', $payload);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send sensor height to IoT Server',
                'error' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('IoT Server Connection Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to connect to IoT Server',
                'error' => $e->getMessage()
            ], 500);
        }
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
