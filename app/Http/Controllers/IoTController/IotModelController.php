<?php

namespace App\Http\Controllers\IotController;

use App\Http\Controllers\Controller;
use App\Models\IotDeviceToken;
use App\Models\IotModel;
use App\Http\Requests\StoreIotModelRequest;
use App\Http\Requests\UpdateIotModelRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\IoTWaterlevelResources;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class IotModelController extends Controller
{

    /**
     * Retrieve all IoT models.
     *
     * This method fetches all IoT model data without any conditions applied. The
     * IoT data is returned wrapped in a IoTWaterlevelResources object.
     *
     * @return IoTWaterlevelResources
     */
    public function index()
    {
        $iotData = IotModel::all();
        return new IoTWaterlevelResources(true, 'Data IoT Berhasil Ditemukan!', $iotData);
    }

    /**
     * Retrieve all IoT models with pagination.
     *
     * This method fetches IoT model data with pagination applied. The paginated 
     * IoT data is returned wrapped in a IoTWaterlevelResources object.
     *
     * @return IoTWaterlevelResources
     */

    /**
     * Retrieve all IoT models with pagination.
     *
     * This method fetches IoT model data with pagination applied. The paginated 
     * IoT data is returned wrapped in a IoTWaterlevelResources object.
     *
     * @param int $perPage The number of items to show per page. Defaults to 10.
     * @return IoTWaterlevelResources
     */
    public function getAll()
    {
        // $iotData = IotModel::paginate(1);
        $perPage = request()->input('perPage', 10);
        // TODO perbarui filter untuk lebih fleksibel. Source : https://www.youtube.com/watch?v=8hhaAsRFAJs&list=PLFIM0718LjIW1Xb7cVj7LdAr32ATDQMdr&index=16
        $iotData = IotModel::filter()->paginate(perPage: $perPage);
        return new IoTWaterlevelResources(true, 'Data IoT Berhasil Ditemukan!', $iotData);
    }

    /**
     * Store a newly created resource in storage.
     */
    // TODO: gunakan package shpjs untuk parsing shapefile to geojson. https://www.npmjs.com/package/shpjs
    // TODO: tambahkan logic untuk generate alamat berdasarkan latitude dan longitude
    public function createDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'location' => 'required',
            'warning_level' => 'required|numeric',
            'danger_level' => 'required|numeric',
            'sensor_height' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $iotDevice = IotModel::create([
            'device_id' => Str::uuid(), // auto-generate UUID
            'device_name' => $request->device_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            "location" => $request->location,
            "warning_level" => $request->warning_level,
            "danger_level" => $request->danger_level,
            "sensor_height" => $request->sensor_height,
        ]);
        return new IoTWaterlevelResources(true, 'Data IoT Berhasil Ditambahkan!', $iotDevice);
    }

    /**
     * Generate a one-time token for device registration.
     *
     * This method generates a one-time token for device registration. The token is
     * unique and will expire in 10 minutes.
     *
     * @param Request $request Request object containing header and body of the request.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the generated token.
     */
    public function generateToken(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|exists:iot_devices,device_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Ambil device
            $device = IotModel::where('device_id', $request->device_id)->first();

            // Buat token
            $token = Str::random(40); // random versi string

            $tokenData = IotDeviceToken::create([
                'device_token' => $token,
                'device_id' => $device->device_id,
                'expired_at' => Carbon::now()->addMinutes(10),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token generated successfully.',
                'data' => $tokenData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token generation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerDevice(Request $request)
    {
        // Validasi input
        $request->validate([
            'device_id' => 'required|string',
        ]);

        // Cari device berdasarkan device_id
        $device = IotModel::where('device_id', $request->device_id)->first();

        if (!$device) {
            return new IoTWaterlevelResources(false, 'IoT Device Not Found!', null, 404);
        }

        // Cari token yang belum digunakan dan belum expired
        $validToken = $device->iotDeviceToken()
            ->whereNull('used_at')
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$validToken) {
            return new IoTWaterlevelResources(false, 'No valid token found for this device.', null, 403);
        }
        $payload = [
            'device_id' => $device->device_id,
            'warning_level' => $device->warning_level,
            'danger_level' => $device->danger_level,
            'sensor_height' => $device->sensor_height,
            'device_token' => $validToken->device_token,
        ];
        $iotServerUrl = env('IOT_SERVER_URL') . '/api/iot/register-device';

        try {
            $response = Http::timeout(5)->post($iotServerUrl, $payload);
            // dd($response->json());

            if ($response->successful()) {
                $validToken->update([
                    'used_at' => Carbon::now(),
                    'status' => 'used',
                ]);
                $validToken->save();
                $device->update([
                    'status' => 'active',
                ]);
                $device->save();
                return new IoTWaterlevelResources(true, 'Device registered successfully.', [
                    'device' => $device,
                ]);
            }

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
    // TODO buat scheduler untuk mengecek mengubah status token ketika waktu expired

    /**
     * Membuat, menghasilkan token, dan mendaftarkan perangkat ke IoT Gateway dalam satu proses.
     */
    public function createAndRegisterDevice(Request $request)
    {
        // 1. Validasi Input Awal
        $validator = Validator::make($request->all(), [
            'device_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location' => 'required|string',
            'warning_level' => 'required|numeric',
            'danger_level' => 'required|numeric',
            'sensor_height' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $device = null;
        $tokenData = null;

        try {
            DB::transaction(function () use ($request, &$device, &$tokenData) {
                $device = IotModel::create([
                    'device_id' => Str::uuid(),
                    'device_name' => $request->device_name,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    "location" => $request->location,
                    "warning_level" => $request->warning_level,
                    "danger_level" => $request->danger_level,
                    "sensor_height" => $request->sensor_height,
                    'status' => 'pending', // Status awal
                ]);

                // Buat token untuk device baru
                $tokenData = IotDeviceToken::create([
                    'device_token' => Str::random(40),
                    'device_id' => $device->device_id,
                    'expired_at' => Carbon::now()->addMinutes(10),
                ]);
            });

            $payload = [
                'device_id' => $device->device_id,
                'warning_level' => $device->warning_level,
                'danger_level' => $device->danger_level,
                'sensor_height' => $device->sensor_height,
                'device_token' => $tokenData->device_token,
            ];

            $iotServerUrl = env('IOT_SERVER_URL') . '/api/iot/register-device';
            $response = Http::timeout(10)->post($iotServerUrl, $payload);

            // 4. Proses respons dari Gateway
            if ($response->successful()) {
                // Jika berhasil, update status device dan token
                $device->update(['status' => 'active']);
                $tokenData->update(['used_at' => Carbon::now(), 'status' => 'used']);

                return new IoTWaterlevelResources(true, 'Perangkat berhasil dibuat dan didaftarkan!', [
                    'device' => $device,
                    'token' => $tokenData->device_token // Kirim token agar user bisa simpan
                ]);
            }


            // Jika GAGAL, beri tahu user tapi jangan hapus data yang sudah dibuat
            return response()->json([
                'success' => false,
                'message' => 'Perangkat dibuat tetapi gagal mendaftar ke IoT Server. Silakan coba lagi.',
                'data' => [
                    'device' => $device,
                    'token' => $tokenData->device_token
                ]
            ], 400); // Bad Request or appropriate error code

        } catch (\Exception $e) {
            Log::error('Device Registration Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal saat mendaftarkan perangkat.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint untuk mencoba ulang registrasi ke IoT Gateway jika sebelumnya gagal.
     */
    public function retryRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|exists:iot_devices,device_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        // Cari device berdasarkan device_id
        $device = IotModel::where('device_id', $request->device_id)->first();

        if (!$device) {
            return new IoTWaterlevelResources(false, 'IoT Device Not Found!', null, 404);
        }

        // Cari token yang belum digunakan dan belum expired
        $validToken = $device->iotDeviceToken()
            ->whereNull('used_at')
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$validToken) {
            return new IoTWaterlevelResources(false, 'No valid token found for this device.', null, 403);
        }
        $payload = [
            'device_id' => $device->device_id,
            'warning_level' => $device->warning_level,
            'danger_level' => $device->danger_level,
            'sensor_height' => $device->sensor_height,
            'device_token' => $validToken->device_token,
        ];
        $iotServerUrl = env('IOT_SERVER_URL') . '/api/iot/register-device';

        try {
            $response = Http::timeout(5)->post($iotServerUrl, $payload);
            // dd($response->json());
            if ($response->successful()) {
                $validToken->update([
                    'used_at' => Carbon::now(),
                    'status' => 'used',
                ]);
                $validToken->save();
                $device->update([
                    'status' => 'active',
                ]);
                $device->save();
                return new IoTWaterlevelResources(true, 'Device registered successfully.', [
                    'device' => $device,
                ]);
            }

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


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // dd($id);
        $device = IotModel::where('device_id', $id)
            ->first();

        if (!$device) {
            return new IoTWaterlevelResources(false, 'Iot Not Found!', null, 404);
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
        return new IoTWaterlevelResources(
            true,
            'Device is authorized!',
            null
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Resources\IoTWaterlevelResources
     */

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
     * Return a list of whitelisted device IDs.
     * 
     * This functionality is not implemented yet.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function whitelistDevice()
    {
        // Mengambil semua device dari model IotModel
        $devices = IotModel::where('status', 'active')->get(); // Gunakan all() untuk mengambil semua data

        // Memeriksa apakah ada device yang ditemukan
        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No devices found.',
            ], 404);
        }

        // Mengambil device_id dari setiap device dan menyimpannya dalam array
        $deviceIds = $devices->pluck('device_id')->toArray();
        return response()->json([
            'device_ids' => $deviceIds, // Mengembalikan array device_id
            'status' => "success",
            'message' => 'Whitelist device functionality is not implemented yet.',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $device = IotModel::where('device_id', $id)->first();

        if (!$device) {
            return new IoTWaterlevelResources(false, 'Device not found.', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'device_name' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'location' => 'sometimes|string',
            'status' => ['sometimes', 'string', Rule::in(['active', 'deactive'])],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $device->update($validator->validated());

        return new IoTWaterlevelResources(true, 'Device updated successfully.', $device);
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

    public function getDashboardStats()
    {
        try {
            // Hitung total perangkat

            $totalDevices = IotModel::count();
            // dd($totalDevices);

            // Hitung perangkat berdasarkan status 'active' atau 'deactive'
            $activeDevices = IotModel::where('status', 'active')->count();
            // dd($activeDevices);
            $inactiveDevices = IotModel::where('status', 'deactive')->count();


            // Asumsi: Anda memiliki cara untuk menentukan level bahaya.
            // Contoh di bawah ini mengasumsikan ada kolom 'last_water_level' dan 'danger_level' di tabel Anda.
            // Anda perlu menyesuaikan query ini dengan logika bisnis Anda yang sebenarnya.
            // $alertingDevices = IotModel::where('status', 'active')
            //     ->whereRaw('last_water_level >= danger_level')
            //     ->count();

            // $warningDevices = IotModel::where('status', 'active')
            //     ->whereRaw('last_water_level >= warning_level AND last_water_level < danger_level')
            //     ->count();

            $stats = [
                'totalDevices' => $totalDevices,
                'activeDevices' => $activeDevices,
                'inactiveDevices' => $inactiveDevices,
                // 'alertingDevices' => $alertingDevices + $warningDevices, // Total yang bermasalah
                // 'dangerCount' => $alertingDevices,
                // 'warningCount' => $warningDevices
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dashboard statistics fetched successfully.',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch dashboard stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve dashboard statistics.'
            ], 500);
        }
    }
}
