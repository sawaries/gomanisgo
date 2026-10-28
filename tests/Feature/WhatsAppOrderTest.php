<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Kategori;
use App\Models\OrderDraft;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Services\OrderService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppOrderTest extends TestCase
{
    use RefreshDatabase;

    protected Produk $produkBeras;

    protected Produk $produkMinyak;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.whatsapp.verify_token' => 'test_verify_token_123']);

        $kat = Kategori::create([
            'nama_kategori' => 'Sembako Utama',
            'deskripsi' => 'Kategori sembako',
        ]);

        $this->produkBeras = Produk::create([
            'id_kategori' => $kat->id_kategori,
            'nama_produk' => 'Beras Rojo Lele 5kg',
            'sku' => 'BRS-RL-05',
            'satuan' => 'karung',
            'harga' => 80000,
            'stok' => 10,
            'status' => true,
        ]);

        $this->produkMinyak = Produk::create([
            'id_kategori' => $kat->id_kategori,
            'nama_produk' => 'Minyak Bimoli 2L',
            'sku' => 'MYK-BM-02',
            'satuan' => 'pouch',
            'harga' => 38000,
            'stok' => 2,
            'status' => true,
        ]);
    }

    public function test_verifikasi_webhook_whatsapp_sukses(): void
    {
        $response = $this->get('/webhook/whatsapp?hub_mode=subscribe&hub_verify_token=test_verify_token_123&hub_challenge=1122334455');

        $response->assertStatus(200);
        $response->assertSee('1122334455');
    }

    public function test_verifikasi_webhook_whatsapp_gagal_jika_token_salah(): void
    {
        $response = $this->get('/webhook/whatsapp?hub_mode=subscribe&hub_verify_token=token_salah&hub_challenge=1122334455');

        $response->assertStatus(403);
    }

    public function test_alur_pemesanan_whatsapp_draft_hingga_konfirmasi_dan_auto_register_user(): void
    {
        $orderService = app(OrderService::class);
        $waPhone = '081234567899';
        $senderName = 'Hendra Pratama';

        // 1. Pelanggan memesan 2 karung Beras
        $draftResult = $orderService->createDraft($waPhone, [
            [
                'id_produk' => $this->produkBeras->id_produk,
                'jumlah' => 2,
            ],
        ], $senderName);

        $this->assertTrue($draftResult['success']);
        $this->assertNotNull($draftResult['draft']);
        $draft = $draftResult['draft'];

        $this->assertEquals(160000, (float) $draft->total);
        $this->assertEquals('menunggu_konfirmasi', $draft->status);

        // 2. Pelanggan mengirim konfirmasi
        $confirmResult = $orderService->confirmAndCreateOrder($draft);

        $this->assertTrue($confirmResult['success'], $confirmResult['pesan'] ?? 'confirm failed');
        $this->assertNotNull($confirmResult['transaksi']);

        $transaksi = $confirmResult['transaksi'];
        $this->assertEquals(160000, (float) $transaksi->total_harga);
        $this->assertEquals('whatsapp', $transaksi->sumber);
        $this->assertEquals($waPhone, $transaksi->nomor_wa);
        $this->assertEquals('diproses', $transaksi->status);

        // 3. User baru harus otomatis terbuat
        $user = User::where('no_telepon', $waPhone)->first();
        $this->assertNotNull($user);
        $this->assertEquals($senderName, $user->nama_user);
        $this->assertEquals($user->id_user, $transaksi->id_user);

        // 4. Stok beras harus berkurang 2 karung (dari 10 menjadi 8)
        $this->produkBeras->refresh();
        $this->assertEquals(8, $this->produkBeras->stok);

        // 5. Draft harus ditandai sebagai selesai
        $draft->refresh();
        $this->assertEquals('selesai', $draft->status);
    }

    public function test_pesanan_gagal_jika_stok_tidak_mencukupi(): void
    {
        $orderService = app(OrderService::class);
        $waPhone = '081299998888';

        // Coba pesan 5 pouch minyak (stok cuma 2)
        $draftResult = $orderService->createDraft($waPhone, [
            [
                'id_produk' => $this->produkMinyak->id_produk,
                'jumlah' => 5,
            ],
        ]);

        $this->assertFalse($draftResult['success']);
        $this->assertNull($draftResult['draft']);
        $this->assertStringContainsString('tidak mencukupi', $draftResult['pesan']);

        // Pastikan tidak ada transaksi yang dibuat
        $this->assertEquals(0, Transaksi::count());
    }

    public function test_pembatalan_pesanan_whatsapp(): void
    {
        $orderService = app(OrderService::class);
        $waPhone = '081211112222';

        $draftResult = $orderService->createDraft($waPhone, [
            [
                'id_produk' => $this->produkBeras->id_produk,
                'jumlah' => 1,
            ],
        ]);

        $this->assertTrue($draftResult['success']);

        // Batalkan draft
        $canceled = $orderService->cancelDraft($waPhone);
        $this->assertTrue($canceled);

        $draft = OrderDraft::where('sender_phone', $waPhone)->latest('id')->first();
        $this->assertEquals('dibatalkan', $draft->status);

        // Pastikan tidak ada pesanan dibuat
        $this->assertEquals(0, Transaksi::count());
    }

    public function test_deduplikasi_pesan_webhook_whatsapp(): void
    {
        $waService = app(WhatsAppService::class);
        $messageId = 'wamid.HBgLMTIzNDU2Nzg5MA==';
        $phone = '081277778888';

        // Pesan pertama
        $waService->processIncomingMessage($messageId, $phone, 'Halo ada beras?');
        $this->assertEquals(1, WhatsappMessage::where('whatsapp_message_id', $messageId)->count());

        // Pesan kedua dengan ID sama (duplikat Meta)
        $waService->processIncomingMessage($messageId, $phone, 'Halo ada beras?');
        $this->assertEquals(1, WhatsappMessage::where('whatsapp_message_id', $messageId)->count());
    }

    public function test_pesanan_whatsapp_muncul_pada_dashboard_admin(): void
    {
        $orderService = app(OrderService::class);
        $waPhone = '081233445566';
        $senderName = 'Rudi Admin Test';

        $draftResult = $orderService->createDraft($waPhone, [
            [
                'id_produk' => $this->produkBeras->id_produk,
                'jumlah' => 1,
            ],
        ], $senderName);

        $orderService->confirmAndCreateOrder($draftResult['draft']);

        // Buat admin
        $admin = Admin::create([
            'nama_user' => 'Admin Toko',
            'email' => 'admin@tokosembwko.com',
            'no_telepon' => '081111111111',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.pesanan.index'));

        $response->assertStatus(200);
        $response->assertSee('WhatsApp');
        $response->assertSee($waPhone);
        $response->assertSee('Rudi Admin Test');
    }
}
