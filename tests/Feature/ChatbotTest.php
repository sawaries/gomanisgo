<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan data dummy produk
        $kat = Kategori::create([
            'nama_kategori' => 'Beras & Biji-bijian',
            'deskripsi' => 'Kategori beras pilihan',
        ]);

        Produk::create([
            'id_kategori' => $kat->id_kategori,
            'nama_produk' => 'Beras Pandan Wangi 5kg',
            'sku' => 'BRS-PW-05',
            'satuan' => 'karung',
            'harga' => 75000,
            'stok' => 20,
            'status' => true,
        ]);

        Produk::create([
            'id_kategori' => $kat->id_kategori,
            'nama_produk' => 'Minyak Goreng Sawit 2L',
            'sku' => 'MYK-SW-02',
            'satuan' => 'pouch',
            'harga' => 34000,
            'stok' => 0, // stok habis
            'status' => true,
        ]);
    }

    public function test_guest_tidak_bisa_mengakses_endpoint_chatbot(): void
    {
        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'Halo apakah ada beras?',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_login_dapat_mengirim_pesan_ke_chatbot_dengan_mock_gemini(): void
    {
        $user = User::create([
            'nama_user' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'password' => bcrypt('password123'),
        ]);

        // Mock Gemini API Response
        config(['services.gemini.api_key' => 'fake-gemini-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Halo Budi! Beras Pandan Wangi 5kg tersedia dengan harga Rp 75.000 per karung (stok 20 karung). Mau pesan berapa karung?'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user, 'web')->postJson(route('chatbot.message'), [
            'message' => 'Ada beras apa saja?',
            'history' => [],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'reply' => 'Halo Budi! Beras Pandan Wangi 5kg tersedia dengan harga Rp 75.000 per karung (stok 20 karung). Mau pesan berapa karung?',
            'user' => 'Budi Santoso',
        ]);
    }

    public function test_fallback_lokal_berfungsi_saat_api_key_tidak_dikonfigurasi(): void
    {
        $user = User::create([
            'nama_user' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'no_telepon' => '081298765432',
            'password' => bcrypt('password123'),
        ]);

        config(['services.gemini.api_key' => null]);

        $response = $this->actingAs($user, 'web')->postJson(route('chatbot.message'), [
            'message' => 'Beras Pandan',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Beras Pandan Wangi 5kg');
    }

    public function test_product_service_mendeteksi_stok_habis(): void
    {
        $service = new ProductService;
        $produkHabis = Produk::where('nama_produk', 'like', '%Minyak%')->first();

        $cek = $service->cekKetersediaan($produkHabis->id_produk, 1);
        $this->assertFalse($cek['tersedia']);
        $this->assertStringContainsString('tidak mencukupi', $cek['pesan']);
    }

    public function test_validasi_panjang_pesan_maksimal(): void
    {
        $user = User::create([
            'nama_user' => 'Doni',
            'email' => 'doni@example.com',
            'no_telepon' => '08111223344',
            'password' => bcrypt('password123'),
        ]);

        $longMessage = str_repeat('a', 501);

        $response = $this->actingAs($user, 'web')->postJson(route('chatbot.message'), [
            'message' => $longMessage,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }
}
