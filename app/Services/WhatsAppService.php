<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    protected ?string $accessToken;

    protected ?string $phoneNumberId;

    protected ?string $verifyToken;

    protected ?string $appSecret;

    protected string $apiVersion;

    public static ?string $lastSentMessage = null;

    public static function getLastSentMessage(): ?string
    {
        return static::$lastSentMessage;
    }

    public function __construct(
        protected OrderService $orderService,
        protected ProductService $productService,
        protected GeminiService $geminiService
    ) {
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->verifyToken = config('services.whatsapp.verify_token');
        $this->appSecret = config('services.whatsapp.app_secret');
        $this->apiVersion = config('services.whatsapp.api_version', 'v21.0');
    }

    /**
     * Verifikasi webhook subscription dari Meta Developer Dashboard.
     */
    public function verifyWebhook(string $mode, string $token, string $challenge): ?string
    {
        if ($mode === 'subscribe' && $token === $this->verifyToken) {
            return $challenge;
        }

        return null;
    }

    /**
     * Verifikasi keaslian signature webhook Meta (X-Hub-Signature-256).
     */
    public function verifySignature(Request $request): bool
    {
        if (empty($this->appSecret)) {
            // Jika app secret tidak dikonfigurasi di environment lokal/testing, loloskan
            return true;
        }

        $signature = $request->header('X-Hub-Signature-256');
        if (! $signature || ! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $expectedSignature = 'sha256='.hash_hmac('sha256', $request->getContent(), $this->appSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Kirim balasan pesan teks ke nomor WhatsApp melalui Meta Cloud API.
     */
    public function sendTextMessage(string $recipientPhone, string $message): array
    {
        // Simpan pesan terakhir yang dikirim untuk keperluan simulasi/testing
        self::$lastSentMessage = $message;

        // Normalisasi nomor telepon tujuan (hilangkan simbol + dan spasi)
        $cleanPhone = preg_replace('/[^0-9]/', '', $recipientPhone);

        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            Log::info("WhatsApp Outgoing (Mock/Belum Dikonfigurasi) to {$cleanPhone}: {$message}");

            return [
                'success' => true,
                'mock' => true,
                'message' => 'WhatsApp credentials not set, message logged locally.',
            ];
        }

        $endpoint = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(12)
                ->post($endpoint, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $cleanPhone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('WhatsApp API Send Error', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->body(),
            ];
        } catch (Throwable $e) {
            Log::error('WhatsApp Send Exception: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Proses pesan teks yang diterima dari webhook WhatsApp.
     */
    public function processIncomingMessage(string $messageId, string $senderPhone, string $messageBody, ?string $senderName = null): void
    {
        // Deduplikasi: jika pesan sudah ada, jangan proses ulang
        if (WhatsappMessage::where('whatsapp_message_id', $messageId)->exists()) {
            Log::info("Pesan WhatsApp duplikat diabaikan: {$messageId}");

            return;
        }

        // Simpan metadata pesan masuk
        $messageRecord = WhatsappMessage::create([
            'whatsapp_message_id' => $messageId,
            'sender_phone' => $senderPhone,
            'message_type' => 'text',
            'message_content' => $messageBody,
            'processing_status' => 'received',
            'received_at' => now(),
        ]);

        $text = trim($messageBody);
        $upperText = strtoupper($text);

        // 1. Alur Pembatalan: BATAL
        if ($upperText === 'BATAL') {
            $canceled = $this->orderService->cancelDraft($senderPhone);
            $reply = $canceled
                ? "Draft pesanan Anda telah dibatalkan. 🙏\nSilakan sebutkan produk sembako lain jika Anda ingin mencari barang lainnya."
                : 'Tidak ada pesanan aktif yang sedang menunggu konfirmasi.';

            $this->sendTextMessage($senderPhone, $reply);
            $messageRecord->update(['processing_status' => 'processed', 'processed_at' => now()]);

            return;
        }

        // 2. Alur Konfirmasi: KONFIRMASI
        if ($upperText === 'KONFIRMASI' || $upperText === 'YA' || $upperText === 'SETUJU') {
            $activeDraft = $this->orderService->getActiveDraft($senderPhone);

            if (! $activeDraft) {
                $reply = "Saat ini tidak ada pesanan aktif yang menunggu konfirmasi. Silakan beri tahu kami produk sembako apa yang ingin Anda beli (misal: 'Beli beras pandan 1 karung').";
                $this->sendTextMessage($senderPhone, $reply);
                $messageRecord->update(['processing_status' => 'processed', 'processed_at' => now()]);

                return;
            }

            $result = $this->orderService->confirmAndCreateOrder($activeDraft);

            if ($result['success']) {
                $tx = $result['transaksi'];
                $totalRp = 'Rp '.number_format((float) $tx->total_harga, 0, ',', '.');
                $reply = "Pesanan Anda berhasil dibuat! 🎉\n\n"
                    ."Nomor Pesanan: *#{$tx->kode}*\n"
                    ."Total: *{$totalRp}*\n"
                    ."Status: *Diproses (Menunggu disiapkan di toko)*\n"
                    ."Metode Pembayaran: *COD (Bayar saat ambil di toko)*\n\n"
                    .'Terima kasih telah berbelanja di *TokoSembwko*! Kami akan menyiapkan pesanan Anda segera.';
            } else {
                $reply = "Mohon maaf, konfirmasi pesanan tidak dapat diselesaikan:\n{$result['pesan']}\n\nSilakan ajukan pesanan baru.";
            }

            $this->sendTextMessage($senderPhone, $reply);
            $messageRecord->update(['processing_status' => 'processed', 'processed_at' => now()]);

            return;
        }

        // 3. Deteksi apakah pesan adalah pemesanan eksplisit (misal "beli/pesan [produk] [jumlah]")
        $orderIntent = $this->detectOrderIntent($text);

        if ($orderIntent['is_order'] && ! empty($orderIntent['items'])) {
            $draftResult = $this->orderService->createDraft($senderPhone, $orderIntent['items'], $senderName);

            if ($draftResult['success']) {
                $this->sendTextMessage($senderPhone, $draftResult['ringkasan_teks']);
            } else {
                $this->sendTextMessage($senderPhone, "Maaf, pesanan belum dapat diproses:\n{$draftResult['pesan']}");
            }

            $messageRecord->update(['processing_status' => 'processed', 'processed_at' => now()]);

            return;
        }

        // 4. Tanya jawab produk biasa menggunakan Gemini AI
        $contextTambahan = "Nomor WhatsApp Pengirim: {$senderPhone}\nNama: ".($senderName ?: 'Pelanggan WhatsApp');
        $aiResult = $this->geminiService->generateReply($text, [], $contextTambahan);

        $reply = $aiResult['reply'] ?? 'Halo! Ada yang bisa kami bantu seputar kebutuhan sembako hari ini?';
        $this->sendTextMessage($senderPhone, $reply);

        $messageRecord->update(['processing_status' => 'processed', 'processed_at' => now()]);
    }

    /**
     * Deteksi niat pemesanan dari kalimat teks pelanggan.
     *
     * @return array{is_order: bool, items: array<int, array{id_produk: int, jumlah: int}>}
     */
    public function detectOrderIntent(string $text): array
    {
        return $this->orderService->detectOrderIntent($text);
    }
}
