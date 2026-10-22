<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{HomeController, KeranjangController, CheckoutController, TransaksiController, AuthController};
use App\Http\Controllers\Admin\{DashboardController, PenjualanController, ProdukController, KategoriController, PesananController, AuthController as AdminAuthController};

Route::middleware('toko')->group(function(){
    Route::get('/', [HomeController::class,'index'])->name('home');

    Route::get('/login',[AuthController::class,'loginForm'])->name('login');
    Route::post('/login',[AuthController::class,'login']);
    Route::get('/register',[AuthController::class,'registerForm'])->name('register');
    Route::post('/register',[AuthController::class,'register']);

    Route::middleware('auth:web')->group(function(){
        Route::post('/keranjang/add/{id}', [KeranjangController::class,'add'])->name('keranjang.add');
        Route::get('/keranjang', [KeranjangController::class,'index'])->name('keranjang.index');
        Route::delete('/keranjang/{id}', [KeranjangController::class,'remove'])->name('keranjang.remove');
        Route::put('/keranjang/{id}', [KeranjangController::class,'update'])->name('keranjang.update');
        Route::post('/keranjang/hapus-pilih', [KeranjangController::class,'hapusPilih'])->name('keranjang.hapus-pilih');
        Route::post('/checkout/konfirmasi', [CheckoutController::class,'konfirmasi'])->name('checkout.konfirmasi');
        Route::post('/checkout', [CheckoutController::class,'process'])->name('checkout.process');
        Route::get('/transaksi', [TransaksiController::class,'index'])->name('transaksi.index');
        Route::post('/transaksi/{id}/batal', [TransaksiController::class,'batal'])->name('transaksi.batal');
    });
});
Route::post('/logout',[AuthController::class,'logout'])->name('logout');

Route::redirect('/admin','/admin/dashboard');
Route::get('/admin/login',[AdminAuthController::class,'loginForm'])->name('admin.login');
Route::post('/admin/login',[AdminAuthController::class,'login']);
Route::post('/admin/logout',[AdminAuthController::class,'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function(){
    Route::get('dashboard', [DashboardController::class,'index'])->name('dashboard');
    Route::get('penjualan', [PenjualanController::class,'index'])->name('penjualan.index');
    Route::get('penjualan/export', [PenjualanController::class,'export'])->name('penjualan.export');
    Route::post('produk/bulk', [ProdukController::class,'bulk'])->name('produk.bulk');
    Route::resource('produk', ProdukController::class)->except(['create','edit','show']);
    Route::resource('kategori', KategoriController::class)->except(['create','edit','show']);
    Route::get('pesanan', [PesananController::class,'index'])->name('pesanan.index');
    Route::put('pesanan/{id}/status', [PesananController::class,'updateStatus'])->name('pesanan.status');
});