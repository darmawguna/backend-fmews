<?php

namespace App\Http\Controllers;

use App\Models\IotDeviceToken;
use App\Models\IotModel;
use App\Models\OtaFirmware;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
class OtaController extends Controller
{
    

    /**
     * Handle the uploading and registration of an OTA file.
     *
     * This method validates the incoming request for an OTA file, stores the file in 
     * a specified directory, and optionally deactivates previous firmware versions if 
     * the current file is set as the latest. It then saves the firmware metadata to 
     * the database and returns a JSON response indicating success.
     *
     * @param Request $request The HTTP request object containing the OTA file and metadata.
     * 
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success and 
     * providing the firmware metadata.
     */

    public function uploadOtaFile(Request $request)
    {
        $request->validate([
            'ota_file' => 'required|file|mimes:txt,bin,sh|max:2048',
            'version' => 'nullable|string',
            'set_as_latest' => 'boolean'
        ]);

        $file = $request->file('ota_file');
        $filename = 'ota_' . now()->timestamp . '.' . $file->getClientOriginalExtension();
        $file->storeAs('ota', $filename);

        // Optional: nonaktifkan versi lama jika di-set sebagai versi terbaru
        if ($request->boolean('set_as_latest')) {
            OtaFirmware::where('is_latest', true)->update(['is_latest' => false]);
        }

        // Simpan metadata ke DB
        $firmware = OtaFirmware::create([
            'filename' => $filename,
            'version' => $request->version,
            'is_latest' => $request->boolean('set_as_latest', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTA file uploaded and registered.',
            'firmware' => $firmware,
        ]);
    }


    /**
     * Download the latest OTA file for a device given a token.
     *
     * This method takes a token as input, verifies the token, and returns the latest
     * OTA firmware for the device associated with the token. The firmware is packaged
     * in a ZIP file along with a JSON configuration file containing the device's
     * ID, latitude, longitude, MQTT topic, and threshold.
     *
     * @param string $token The device token to verify and fetch the firmware for.
     *
     * @return \Illuminate\Http\Response A response containing the ZIP file.
     */
    public function downloadOTA($token)
    {
        // 1. Cek validitas token
        $deviceToken = IotDeviceToken::where('device_token', $token)->where('status', 'unused')->first();
        if (!$deviceToken) {
            return response()->json(['error' => 'Token tidak valid atau sudah digunakan'], 403);
        }

        // 2. Ambil informasi device
        $device = IotModel::where('device_id', $deviceToken->device_id)->first();
        if (!$device) {
            return response()->json(['error' => 'Device tidak ditemukan'], 404);
        }

        // 3. Tandai token sebagai 'used'
        $deviceToken->status = 'used';
        $deviceToken->save();

        // 4. Generate file konfigurasi JSON
        $config = [
            'device_id' => $device->device_id,
            'latitude' => $device->latitude,
            'longitude' => $device->longitude,
            // TODO : perbarui configurasi sesuai kebutuhan firmware yang akan dibuat nanti
            'mqtt_topic' => "iot/{$device->device_id}/data",
            'threshold' => 25
        ];
        $configFilename = "ota/configs/device_config_{$device->device_id}.json";
        Storage::put($configFilename, json_encode($config, JSON_PRETTY_PRINT));

        // 5. Siapkan firmware
        // $firmwarePath = storage_path('app/ota/firmware.bin');
        $firmware = OtaFirmware::where('is_latest', true)->first();
        if ($firmware) {
            $firmwarePath = storage_path("app/ota/{$firmware->filename}");
        } else {
            return response()->json(['error' => 'Firmware tidak ditemukan'], 404);
        }
        if (!file_exists($firmwarePath)) {
            return response()->json(['error' => 'Firmware tidak tersedia'], 500);
        }

        // 6. Buat ZIP file
        $zipFilename = storage_path("app/ota/OTA_{$device->device_id}.zip");
        $zip = new ZipArchive;
        if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $zip->addFile($firmwarePath, 'firmware.bin');
            $zip->addFromString('device_config.json', json_encode($config, JSON_PRETTY_PRINT));
            $zip->close();
        } else {
            return response()->json(['error' => 'Gagal membuat file ZIP'], 500);
        }

        // 7. Kirim ZIP ke device
        return response()->make(response()->download($zipFilename)->deleteFileAfterSend(true));
    }
}
