<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {}

    /**
     * Webhook verification endpoint (GET) untuk Meta Developer Dashboard.
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        if (! $mode || ! $token || ! $challenge) {
            return response('Parameter verifikasi tidak lengkap', 400);
        }

        $result = $this->whatsAppService->verifyWebhook($mode, $token, $challenge);

        if ($result !== null) {
            Log::info('WhatsApp Webhook terverifikasi dengan sukses.');

            return response($result, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Gagal verifikasi WhatsApp Webhook: token tidak cocok.');

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook notifications (POST) dari WhatsApp Cloud API.
     */
    public function handle(Request $request): Response
    {
        // 1. Verifikasi keaslian signature jika App Secret dikonfigurasi
        if (! $this->whatsAppService->verifySignature($request)) {
            Log::warning('WhatsApp Webhook signature invalid.');

            return response('Invalid signature', 403);
        }

        $payload = $request->all();

        // 2. Pastikan objek berasal dari WhatsApp Business Account
        if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
            return response('EVENT_RECEIVED', 200);
        }

        try {
            $entries = $payload['entry'] ?? [];
            foreach ($entries as $entry) {
                $changes = $entry['changes'] ?? [];
                foreach ($changes as $change) {
                    $value = $change['value'] ?? [];

                    // Ambil kontak jika tersedia
                    $contacts = $value['contacts'] ?? [];
                    $senderName = $contacts[0]['profile']['name'] ?? null;

                    // Ambil pesan masuk
                    $messages = $value['messages'] ?? [];
                    foreach ($messages as $message) {
                        $messageId = $message['id'] ?? null;
                        $from = $message['from'] ?? null;
                        $type = $message['type'] ?? 'unknown';

                        if (! $messageId || ! $from) {
                            continue;
                        }

                        // Hanya tangani tipe teks (atau audio/interaktif jika ada text)
                        $body = null;
                        if ($type === 'text') {
                            $body = $message['text']['body'] ?? null;
                        } elseif ($type === 'interactive') {
                            $body = $message['interactive']['button_reply']['title']
                                ?? $message['interactive']['list_reply']['title']
                                ?? null;
                        }

                        if ($body !== null) {
                            $this->whatsAppService->processIncomingMessage(
                                messageId: $messageId,
                                senderPhone: $from,
                                messageBody: $body,
                                senderName: $senderName
                            );
                        } else {
                            Log::info("Pesan tipe {$type} tidak didukung langsung, mengirim pesan bantuan.");
                            $this->whatsAppService->sendTextMessage(
                                $from,
                                'Halo! Kami menerima pesan Anda. Saat ini bot kami mendukung pesan teks untuk pengecekan stok dan pemesanan sembako.'
                            );
                        }
                    }
                }
            }

            return response('EVENT_RECEIVED', 200);
        } catch (Throwable $e) {
            Log::error('Kesalahan saat memproses webhook WhatsApp: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Selalu kembalikan 200 ke Meta agar webhook tidak di-retry berulang kali oleh Meta
            return response('EVENT_RECEIVED', 200);
        }
    }
}
