<?php

use App\Models\FacilityCategory;
use App\Models\PublicFacility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFacilityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_public_facilities()
    {
        PublicFacility::factory()->count(5)->create();

        $response = $this->getJson('/api/public-facilities');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['data']]);
    }

    public function test_can_create_public_facility()
    {
        $category = FacilityCategory::factory()->create();

        $payload = [
            'id_kategori' => $category->id,
            'nama_fasilitas' => 'Puskesmas Banjar',
            'alamat' => 'Jl. Raya Seririt',
            'latitude' => -8.3,
            'longitude' => 115.1,
            'deskripsi' => 'Fasilitas kesehatan utama.',
            'status_operasional' => 'Beroperasi'
        ];

        $response = $this->postJson('/api/public-facilities', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['nama_fasilitas' => 'Puskesmas Banjar']);
    }

    public function test_can_show_public_facility()
    {
        $facility = PublicFacility::factory()->create();

        $response = $this->getJson("/api/public-facilities/{$facility->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['nama_fasilitas' => $facility->nama_fasilitas]);
    }

    public function test_can_update_public_facility()
    {
        $facility = PublicFacility::factory()->create();

        $response = $this->putJson("/api/public-facilities/{$facility->id}", [
            'nama_fasilitas' => 'Fasilitas Baru'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['nama_fasilitas' => 'Fasilitas Baru']);
    }

    public function test_can_delete_public_facility()
    {
        $facility = PublicFacility::factory()->create();

        $response = $this->deleteJson("/api/public-facilities/{$facility->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Data Fasilitas Publik Berhasil Dihapus!']);

        $this->assertDatabaseMissing('fasilitas_publik', ['id' => $facility->id]);
    }
}

