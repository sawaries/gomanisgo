<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(
        protected GeminiService $geminiService,
        protected ProductService $productService
    ) {}

    /**
     * Handle incoming chat message from authenticated user on the website.
     */
    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array',
            'history.*.role' => 'nullable|in:user,model',
            'history.*.text' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $message = trim($validated['message']);
        $history = $validated['history'] ?? [];

        $contextTambahan = 'Pelanggan saat ini: '.($user ? $user->nama_user : 'Tamu');

        try {
            $result = $this->geminiService->generateReply($message, $history, $contextTambahan);

            return response()->json([
                'success' => $result['success'],
                'reply' => $result['reply'],
                'user' => $user ? $user->nama_user : null,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'reply' => 'Maaf, terjadi kesalahan pada server saat memproses pesan Anda.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Endpoint cepat untuk mencari produk yang dapat diakses oleh widget jika diperlukan.
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        $produk = $this->productService->cariProduk($q, limit: 10);

        return response()->json([
            'data' => $produk->map(fn ($p) => [
                'id' => $p->id_produk,
                'nama' => $p->nama_produk,
                'harga' => $p->harga,
                'satuan' => $p->satuan,
                'stok' => $p->stok,
                'gambar_url' => $p->gambar_url,
            ]),
        ]);
    }
}
