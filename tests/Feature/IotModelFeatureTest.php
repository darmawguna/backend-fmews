<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\IotModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class IotModelFeatureTest extends TestCase
{
    use RefreshDatabase;

    // Tes untuk endpoint ini sudah sesuai dengan route Anda
    /** @test */
    public function create_and_register_device_successfully()
    {
        Http::fake([env('IOT_SERVER_URL') . '/*' => Http::response(null, 200)]);
        $payload = [
            'device_name' => 'Sensor Teras Depan',
            'latitude' => -8.123,
            'longitude' => 115.456,
            'location' => 'Buleleng',
            'warning_level' => 75,
            'danger_level' => 90,
            'sensor_height' => 120,
        ];

        $response = $this->postJson('/api/iot/create-and-register', $payload);

        $response->assertStatus(200)->assertJsonFragment(['message' => 'Perangkat berhasil dibuat dan didaftarkan!']);
        $this->assertDatabaseHas('Iot_devices', ['device_name' => 'Sensor Teras Depan', 'status' => 'active']);
    }

    // Tes untuk endpoint ini sudah sesuai dengan route Anda
    /** @test */
    public function create_and_register_device_fails_on_iot_server_error()
    {
        Http::fake([env('IOT_SERVER_URL') . '/*' => Http::response(null, 500)]);
        $payload = [
            'device_name' => 'Sensor Gagal',
            'latitude' => -8.123,
            'longitude' => 115.456,
            'location' => 'Buleleng',
            'warning_level' => 75,
            'danger_level' => 90,
            'sensor_height' => 120,
        ];

        $response = $this->postJson('/api/iot/create-and-register', $payload);

        $response->assertStatus(400)->assertJsonFragment(['message' => 'Perangkat dibuat tetapi gagal mendaftar ke IoT Server. Silakan coba lagi.']);
        $this->assertDatabaseHas('Iot_devices', ['device_name' => 'Sensor Gagal', 'status' => 'pending']);
    }

    // Tes untuk endpoint ini sudah sesuai dengan route Anda
    /** @test */
    public function get_all_sensors_with_pagination_and_search()
    {
        IotModel::factory()->create([
            'device_name' => 'Sungai 1',
            'latitude' => -8.123,
            'longitude' => 115.456,
            'location' => 'Buleleng',
            'warning_level' => 75,
            'danger_level' => 90,
            'sensor_height' => 120,
        ]);
        IotModel::factory()->count(10)->create();

        $response = $this->getJson('/api/iot/sensors?search=Sungai');
        $response->assertStatus(200)->assertJsonCount(1, 'data.data')->assertJsonFragment(['device_name' => 'Sungai 1']);

    }

    /** @test */
    public function show_device_data_successfully()
    {
        $device = IotModel::factory()->create();
        // ROUTE UPDATED: Menyesuaikan dengan `GET /api/iot/{id}`
        $response = $this->getJson("/api/iot/{$device->device_id}");
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Iot Data is Found!']);
    }

    /** @test */
    public function update_device_successfully()
    {
        $device = IotModel::factory()->create();
        // ROUTE UPDATED: Menyesuaikan dengan `PUT /api/iot/{id}/update`
        $response = $this->putJson("/api/iot/{$device->device_id}/update", ['device_name' => 'Updated Name']);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Device updated successfully.']);
        $this->assertDatabaseHas('Iot_devices', ['id' => $device->id, 'device_name' => 'Updated Name']);
    }

    /** @test */
    public function destroy_device_successfully()
    {
        $device = IotModel::factory()->create();
        // ROUTE UPDATED: Menyesuaikan dengan `DELETE /api/iot/{id}`
        $response = $this->deleteJson("/api/iot/{$device->device_id}");
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Device soft deleted successfully!']);
        $this->assertSoftDeleted('Iot_devices', ['device_id' => $device->device_id]);
    }

    /** @test */
    public function change_status_successfully()
    {
        $device = IotModel::factory()->create(['status' => 'pending']);
        // ROUTE UPDATED: Menggunakan metode PATCH dan URL yang benar
        $response = $this->patchJson("/api/iot/{$device->device_id}/change-status", ['status' => 'active']);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Update Status successfully!']);
        $this->assertDatabaseHas('Iot_devices', ['id' => $device->id, 'status' => 'active']);
    }

    /** @test */
    public function change_status_fails_with_invalid_status()
    {
        $device = IotModel::factory()->create();
        // ROUTE UPDATED: Menggunakan metode PATCH dan URL yang benar
        $response = $this->patchJson("/api/iot/{$device->device_id}/change-status", ['status' => 'invalid-status']);
        $response->assertStatus(422);
    }

    // Tes di bawah ini sudah sesuai dengan route Anda, tidak perlu diubah
    /** @test */
    public function whitelist_device_returns_all_device_ids()
    {
        IotModel::factory()->count(3)->create();
        $response = $this->getJson('/api/iot/whitelist-device');
        $response->assertStatus(200)->assertJsonStructure(['device_ids'])->assertJsonCount(3, 'device_ids');
    }

    /** @test */
    public function generate_token_successfully()
    {
        $device = IotModel::factory()->create();
        $response = $this->postJson('/api/iot/generate-token', ['device_id' => $device->device_id]);
        $response->assertStatus(200)->assertJsonStructure(['success', 'message', 'data' => ['device_token', 'device_id', 'expired_at']]);
    }

    /** @test */
    public function generate_token_fails_with_invalid_device_id()
    {
        $response = $this->postJson('/api/iot/generate-token', ['device_id' => 'invalid-id']);
        $response->assertStatus(422)->assertJsonPath('errors.device_id.0', 'The selected device id is invalid.');
    }
}