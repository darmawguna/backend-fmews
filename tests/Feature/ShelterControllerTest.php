<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShelterControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_get_shelter_list_with_pagination()
    {
        \App\Models\Shelter::factory()->count(15)->create();

        $response = $this->getJson('/api/shelter?perPage=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    public function test_can_create_shelter()
    {
        $payload = [
            'nama_shelter' => 'Shelter A',
            'alamat' => 'Jl. Raya Denpasar',
            'latitude' => -8.65,
            'longitude' => 115.22,
            'kapasitas_maksimum' => 100,
            'ketersediaan_saat_ini' => 50,
            'status' => 'Buka',
        ];

        $response = $this->postJson('/api/shelter', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['nama_shelter' => 'Shelter A']);
    }

    public function test_can_show_shelter_detail()
    {
        $shelter = \App\Models\Shelter::factory()->create();

        $response = $this->getJson("/api/shelter/{$shelter->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['nama_shelter' => $shelter->nama_shelter]);
    }

    public function test_can_update_shelter()
    {
        $shelter = \App\Models\Shelter::factory()->create([
            'kapasitas_maksimum' => 100
        ]);

        $payload = [
            'nama_shelter' => 'Shelter Updated',
            'ketersediaan_saat_ini' => 80
        ];

        $response = $this->putJson("/api/shelter/{$shelter->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['nama_shelter' => 'Shelter Updated']);
    }

    public function test_update_fails_when_ketersediaan_exceeds_kapasitas()
    {
        $shelter = \App\Models\Shelter::factory()->create([
            'kapasitas_maksimum' => 50
        ]);

        $payload = ['ketersediaan_saat_ini' => 100];

        $response = $this->putJson("/api/shelter/{$shelter->id}", $payload);

        $response->assertStatus(422)
            ->assertJsonFragment(['ketersediaan_saat_ini' => ['Ketersediaan saat ini tidak boleh melebihi kapasitas maksimum.']]);
    }

    public function test_can_delete_shelter()
    {
        $shelter = \App\Models\Shelter::factory()->create();

        $response = $this->deleteJson("/api/shelter/{$shelter->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Data Shelter Berhasil Dihapus!']);

        $this->assertDatabaseMissing('shelters', ['id' => $shelter->id]);

    }

}
