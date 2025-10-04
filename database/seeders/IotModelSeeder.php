<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Pakai Eloquent agar event/cast tetap jalan
use App\Models\IotModel;
use App\Models\IotDeviceToken;

class IotModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan (opsional, sesuaikan kebijakanmu)
        // DB::table('iot_device_tokens')->truncate();
        // DB::table('iot_devices')->truncate();

        // ====== Contoh data device ======
        $now = Carbon::now();

        $devices = [
            [
                'device_id' => (string) Str::uuid(),
                'device_name' => 'Sungai Ayung - Jembatan A',
                'latitude' => -8.492150,
                'longitude' => 115.263550,
                'location' => 'Petang, Badung',
                'warning_level' => 120,      // cm
                'danger_level' => 160,      // cm
                'sensor_height' => 300,      // cm dari dasar sensor ke dasar sungai
                'status' => 'active', // mengikuti controller: active|deactive|pending
            ],
            [
                'device_id' => (string) Str::uuid(),
                'device_name' => 'Sungai Buleleng - DAS B',
                'latitude' => -8.112340,
                'longitude' => 115.083210,
                'location' => 'Buleleng, Bali',
                'warning_level' => 100,
                'danger_level' => 140,
                'sensor_height' => 280,
                'status' => 'deactive',
            ],
            [
                'device_id' => (string) Str::uuid(),
                'device_name' => 'Sungai Unda - Hilir C',
                'latitude' => -8.531200,
                'longitude' => 115.357900,
                'location' => 'Klungkung, Bali',
                'warning_level' => 90,
                'danger_level' => 130,
                'sensor_height' => 250,
                'status' => 'pending',
            ],
        ];

        foreach ($devices as $d) {
            /** @var \App\Models\IotModel $device */
            $device = IotModel::updateOrCreate(
                ['device_id' => $d['device_id']],
                [
                    'device_name' => $d['device_name'],
                    'latitude' => $d['latitude'],
                    'longitude' => $d['longitude'],
                    'location' => $d['location'],
                    'warning_level' => $d['warning_level'],
                    'danger_level' => $d['danger_level'],
                    'sensor_height' => $d['sensor_height'],
                    'status' => $d['status'],
                ]
            );

            // ====== Token seeder mengikuti logika controller ======
            // 1) Token valid (belum digunakan, expired 10 menit ke depan)
            IotDeviceToken::updateOrCreate(
                [
                    'device_token' => Str::random(40),
                ],
                [
                    'device_id' => $device->device_id,
                    'expired_at' => $now->copy()->addMinutes(10),
                    'status' => 'unused',        // biarkan null = belum used (sesuai controller-mu)
                    'used_at' => null,
                ]
            );

            // 2) Token expired (contoh kasus retry akan gagal)
            IotDeviceToken::updateOrCreate(
                [
                    'device_token' => Str::random(40),
                ],
                [
                    'device_id' => $device->device_id,
                    'expired_at' => $now->copy()->subMinutes(5),
                    'status' => 'expired',
                    'used_at' => null,
                ]
            );

            // 3) Token sudah digunakan (hanya contoh data historis)
            IotDeviceToken::updateOrCreate(
                [
                    'device_token' => Str::random(40),
                ],
                [
                    'device_id' => $device->device_id,
                    'expired_at' => $now->copy()->addMinutes(10),
                    'status' => 'used',
                    'used_at' => $now->copy()->subMinute(),
                ]
            );
        }
    }
}

