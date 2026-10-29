<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiService
{
    protected ?string $apiKey;

    protected string $model;

    protected string $apiUrl;

    public function __construct(
        protected ProductService $productService
    ) {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->apiUrl = config('services.gemini.api_url', 'https://generativelanguage.googleapis.com/v1beta');
    }

    /**
     * Kirim percakapan ke Google Gemini API.
     *
     * @param  string  $userMessage  Pesan dari pengguna
     * @param  array  $conversationHistory  Riwayat percakapan sebelumnya [['role' => 'user'|'model', 'text' => '...']]
     * @param  string|null  $contextTambahan  Konteks tambahan jika ada (misal status pesanan)
     * @return array{success: bool, reply: string, error: ?string}
     */
    public function generateReply(string $userMessage, array $conversationHistory = [], ?string $contextTambahan = null): array
    {
        // Validasi input
        $userMessage = trim($userMessage);
        if (empty($userMessage)) {
            return [
                'success' => false,
                'reply' => 'Pesan tidak boleh kosong.',
                'error' => 'empty_message',
            ];
        }

        // Batasi panjang pesan
        if (mb_strlen($userMessage) > 500) {
            $userMessage = mb_substr($userMessage, 0, 500);
        }

        if (empty($this->apiKey)) {
            Log::warning('Gemini API Key belum dikonfigurasi di .env');

            return [
                'success' => false,
                'reply' => $this->fallbackOfflineResponse($userMessage),
                'error' => 'api_key_missing',
            ];
        }

        // Ambil data produk toko untuk dijadikan context fakta bagi AI
        $katalogTeks = $this->productService->formatKatalogTeks();

        $systemInstruction = $this->buildSystemInstruction($katalogTeks, $contextTambahan);
        $contents = $this->buildContentsPayload($conversationHistory, $userMessage);

        $endpoint = "{$this->apiUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemInstruction],
                        ],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 800,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $replyText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($replyText) {
                    return [
                        'success' => true,
                        'reply' => trim($replyText),
                        'error' => null,
                    ];
                }

                Log::warning('Format respons Gemini tidak terduga', ['response' => $data]);

                return [
                    'success' => false,
                    'reply' => 'Maaf, asisten kami sedang kesulitan memproses jawaban saat ini. Silakan coba tanyakan kembali.',
                    'error' => 'invalid_response_format',
                ];
            }

            $status = $response->status();
            $errorBody = $response->json();
            Log::error("Gemini API Error [{$status}]", [
                'error' => $errorBody['error']['message'] ?? $response->body(),
            ]);

            return [
                'success' => false,
                'reply' => $this->fallbackErrorResponse($status),
                'error' => "api_error_{$status}",
            ];
        } catch (Throwable $e) {
            Log::error('Koneksi ke Gemini API Gagal: '.$e->getMessage());

            return [
                'success' => false,
                'reply' => 'Maaf, terjadi gangguan koneksi ke layanan AI. Silakan coba sesaat lagi.',
                'error' => 'connection_exception',
            ];
        }
    }

    /**
     * Susun system prompt instruksi dan kepribadian asisten TokoSembwko.
     */
    protected function buildSystemInstruction(string $katalogTeks, ?string $contextTambahan = null): string
    {
        $prompt = <<<PROMPT
Anda adalah asisten AI resmi toko sembako "TokoSembwko" (GoManisGo).
Tugas utama Anda:
1. Membantu pelanggan mencari informasi sembako dan kebutuhan pokok.
2. Menjelaskan harga, satuan, dan ketersediaan stok HANYA berdasarkan database produk di bawah ini.
3. Membantu pelanggan memilih produk dan menentukan jumlah belanjaan.
4. Bersikap ramah, sopan, ringkas, dan jelas dalam bahasa Indonesia santun.

ATURAN KETAT:
- SUMBER KEBENARAN TUNGGAL: Anda HANYA boleh menyebutkan produk, harga, dan stok yang tertulis pada DATA PRODUK AKTUAL di bawah.
- DILARANG HALUSINASI: Jangan mengarang nama produk, jangan mengarang harga, dan jangan mengarang stok.
- Jika produk tidak ada dalam data, katakan dengan sopan bahwa produk tersebut belum tersedia di TokoSembwko.
- Jika stok habis (STOK HABIS atau 0), sampaikan bahwa stok sedang kosong.
- JANGAN mengklaim bahwa pesanan sudah selesai dibuat atau dibayar kecuali ada konfirmasi resmi dari sistem.
- Jangan menyebutkan ID produk teknis kepada pelanggan kecuali diperlukan sebagai pembeda.
- Jawablah dengan format teks rapi dan mudah dibaca di layar HP.

DATA PRODUK AKTUAL TOKOSEMBWKO:
{$katalogTeks}
PROMPT;

        if ($contextTambahan) {
            $prompt .= "\n\nINFORMASI TAMBAHAN:\n".$contextTambahan;
        }

        return $prompt;
    }

    /**
     * Format payload isi chat untuk Gemini.
     */
    protected function buildContentsPayload(array $history, string $latestMessage): array
    {
        $contents = [];

        // Ambil maksimal 8 riwayat terakhir agar konteks ringkas & hemat token
        $recentHistory = array_slice($history, -8);

        foreach ($recentHistory as $msg) {
            $role = ($msg['role'] ?? 'user') === 'model' ? 'model' : 'user';
            $text = trim($msg['text'] ?? ($msg['content'] ?? ''));

            if (! empty($text)) {
                $contents[] = [
                    'role' => $role,
                    'parts' => [
                        ['text' => $text],
                    ],
                ];
            }
        }

        // Tambahkan pesan terbaru pengguna
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $latestMessage],
            ],
        ];

        return $contents;
    }

    /**
     * Fallback cerdas berbasis pencarian lokal saat API Key belum diisi.
     */
    protected function fallbackOfflineResponse(string $query): string
    {
        $lower = strtolower(trim($query));

        // 1. Deteksi sapaan umum (jika diawali sapaan atau hanya berisi sapaan)
        $greetings = ['halo', 'hai', 'hello', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam', 'assalamualaikum', 'pagi', 'siang', 'sore', 'malam'];
        foreach ($greetings as $g) {
            if ($lower === $g || str_starts_with($lower, $g.' ') || str_starts_with($lower, $g.',') || str_starts_with($lower, $g.'!')) {
                if (mb_strlen($lower) <= mb_strlen($g) + 6) {
                    return "Halo! 👋 Selamat datang di TokoSembwko.\n\n"
                        ."Kami menyediakan berbagai kebutuhan pokok sehari-hari:\n"
                        ."• 🌾 Beras (Pandan Wangi Super, Setra Ramos, Rojolele)\n"
                        ."• 🍳 Minyak Goreng (Bimoli, Sania, SunCo, Tropical)\n"
                        ."• 🧂 Gula Pasir (Gulaku Premium, Rose Brand)\n"
                        ."• 🥚 Telur Ayam Negeri Segar\n"
                        ."• 🍞 Tepung Terigu Segitiga Biru & Garam Beryodium\n"
                        ."• 🍜 Mie Instan Indomie\n\n"
                        .'Ada produk sembako yang ingin Anda cari atau pesan hari ini?';
                }
            }
        }

        $keywords = $this->productService->ekstrakKataKunci($query);

        // 2. Permintaan daftar/katalog umum jika tidak ada kata kunci produk spesifik
        if (empty($keywords)) {
            $semuaProduk = $this->productService->cariProduk(limit: 10);
            $items = [];
            foreach ($semuaProduk as $p) {
                $harga = 'Rp '.number_format((float) $p->harga, 0, ',', '.');
                $stok = $p->stok > 0 ? "stok: {$p->stok} {$p->satuan}" : 'stok habis';
                $items[] = "• {$p->nama_produk} ({$harga} / {$p->satuan}) - {$stok}";
            }

            return "Berikut daftar produk sembako yang tersedia di TokoSembwko:\n\n"
                .implode("\n", $items)
                ."\n\n💡 *Cara Pesan Mudah:* Ketik *pesan [Nama Produk] [Jumlah]* (contoh: *pesan telur ayam 1kg* atau *pesan minyak sania 2*)";
        }

        // 3. Pencarian produk spesifik di database
        $produk = $this->productService->cariProduk($query, limit: 6);

        if ($produk->isEmpty()) {
            return "Mohon maaf, kami belum menemukan produk yang cocok dengan pencarian '{$query}'. 🙏\n\n"
                ."Produk sembako yang sedang ready di toko kami antara lain:\n"
                ."• Telur Ayam Negeri Segar 1 Kg\n"
                ."• Minyak Goreng Bimoli 2L, Sania 2L, SunCo 2L\n"
                ."• Beras Pandan Wangi 5 Kg, Setra Ramos 5 Kg, Rojolele 5 Kg\n"
                ."• Gula Pasir Gulaku 1 Kg, Rose Brand 1 Kg\n"
                ."• Tepung Segitiga Biru 1 Kg & Indomie\n\n"
                .'Silakan ketik nama produk yang ingin Anda cari!';
        }

        $items = [];
        foreach ($produk as $p) {
            $harga = 'Rp '.number_format((float) $p->harga, 0, ',', '.');
            $stok = $p->stok > 0 ? "Stok: {$p->stok} {$p->satuan}" : 'Stok Kosong';
            $items[] = "• *{$p->nama_produk}*\n  Harga: {$harga} / {$p->satuan} | {$stok}";
        }

        return "Halo! Berikut produk yang tersedia di TokoSembwko sesuai pencarian Anda:\n\n"
            .implode("\n\n", $items)
            ."\n\n💡 *Ingin memesan?*\nKetik: *pesan [Nama Produk] [Jumlah]*\nContoh: *pesan telur ayam negeri 1kg* atau *beli minyak bimoli 2*";
    }

    /**
     * Pesan penanganan error status HTTP.
     */
    protected function fallbackErrorResponse(int $statusCode): string
    {
        if ($statusCode === 429) {
            return 'Layanan chatbot kami sedang menerima banyak permintaan. Mohon tunggu beberapa detik sebelum mencoba lagi.';
        }

        return 'Mohon maaf, terjadi kendala saat menghubungkan ke asisten belanja kami. Silakan coba kembali dalam beberapa saat.';
    }
}
