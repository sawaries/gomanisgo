<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\PenjualanController;
use App\Http\Controllers\Admin\PesananController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware('toko')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:web')->group(function () {
        Route::post('/keranjang/add/{id}', [KeranjangController::class, 'add'])->name('keranjang.add');
        Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
        Route::delete('/keranjang/{id}', [KeranjangController::class, 'remove'])->name('keranjang.remove');
        Route::put('/keranjang/{id}', [KeranjangController::class, 'update'])->name('keranjang.update');
        Route::post('/keranjang/hapus-pilih', [KeranjangController::class, 'hapusPilih'])->name('keranjang.hapus-pilih');
        Route::post('/checkout/konfirmasi', [CheckoutController::class, 'konfirmasi'])->name('checkout.konfirmasi');
        Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::post('/transaksi/{id}/batal', [TransaksiController::class, 'batal'])->name('transaksi.batal');
        Route::post('/chatbot/message', [ChatbotController::class, 'message'])->name('chatbot.message');
        Route::get('/chatbot/search', [ChatbotController::class, 'search'])->name('chatbot.search');
    });
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Webhook WhatsApp Business Cloud API (Meta)
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle'])->name('whatsapp.webhook.handle');

Route::redirect('/admin', '/admin/dashboard');
Route::get('/admin/login', [AdminAuthController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::get('penjualan/export', [PenjualanController::class, 'export'])->name('penjualan.export');
    Route::post('produk/bulk', [ProdukController::class, 'bulk'])->name('produk.bulk');
    Route::resource('produk', ProdukController::class)->except(['create', 'edit', 'show']);
    Route::resource('kategori', KategoriController::class)->except(['create', 'edit', 'show']);
    Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::put('pesanan/{id}/status', [PesananController::class, 'updateStatus'])->name('pesanan.status');
});
