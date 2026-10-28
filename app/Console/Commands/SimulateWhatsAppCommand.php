<?php

namespace App\Console\Commands;

use App\Models\Produk;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SimulateWhatsAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:simulate 
                            {message? : Pesan WhatsApp yang dikirim pelanggan}
                            {--from=081234567890 : Nomor telepon pengirim}
                            {--name=Budi Santoso : Nama pengirim}
                            {--demo : Jalankan simulasi alur lengkap otomatis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasikan alur chat dan pemesanan WhatsApp Bot TokoSembwko';

    public function handle(WhatsAppService $whatsAppService): int
    {
        $message = $this->argument('message');
        $from = $this->option('from');
        $name = $this->option('name');
        $isDemo = $this->option('demo') || empty($message);

        $this->info('==================================================');
        $this->info('   SIMULATOR WHATSAPP BOT TOKOSEMBWKO');
        $this->info('==================================================');
        $this->line("Pengirim : <comment>{$name}</comment> (<info>{$from}</info>)");
        $this->newLine();

        if ($isDemo) {
            return $this->runFullDemo($whatsAppService, $from, $name);
        }

        // Jalankan satu pesan kustom
        $messageId = 'wamid.'.Str::random(20);
        $this->line("<fg=yellow>[PELANGGAN {$from}]:</> {$message}");

        $whatsAppService->processIncomingMessage($messageId, $from, $message, $name);
        $reply = WhatsAppService::getLastSentMessage() ?? '(Tidak ada balasan teks)';

        $this->newLine();
        $this->line("<fg=green>[BOT TOKOSEMBWKO]:</>\n{$reply}");
        $this->newLine();

        return self::SUCCESS;
    }

    protected function runFullDemo(WhatsAppService $whatsAppService, string $from, string $name): int
    {
        // Cari produk yang tersedia
        $produk = Produk::where('status', true)->where('stok', '>=', 2)->first()
            ?? Produk::where('status', true)->first();

        if (! $produk) {
            $this->error('Tidak ada produk aktif di database untuk disimulasikan. Tambahkan produk terlebih dahulu di database.');

            return self::FAILURE;
        }

        $this->line("Menggunakan produk contoh: <comment>{$produk->nama_produk}</comment> (Stok: {$produk->stok})");
        $this->newLine();

        // TAHAP 1: Kirim Pesan Pemesanan
        $pesanOrder = "Halo, saya mau beli {$produk->nama_produk} 2";
        $this->line("<fg=yellow>[LANGKAH 1 - PELANGGAN]:</> {$pesanOrder}");

        $msgId1 = 'demo_msg_'.Str::random(12);
        $whatsAppService->processIncomingMessage($msgId1, $from, $pesanOrder, $name);
        $reply1 = WhatsAppService::getLastSentMessage();

        $this->newLine();
        $this->line("<fg=green>[BOT TOKOSEMBWKO]:</>\n{$reply1}");
        $this->newLine();

        // TAHAP 2: Kirim Konfirmasi
        $this->line('<fg=yellow>[LANGKAH 2 - PELANGGAN]:</> KONFIRMASI');

        $msgId2 = 'demo_msg_'.Str::random(12);
        $whatsAppService->processIncomingMessage($msgId2, $from, 'KONFIRMASI', $name);
        $reply2 = WhatsAppService::getLastSentMessage();

        $this->newLine();
        $this->line("<fg=green>[BOT TOKOSEMBWKO]:</>\n{$reply2}");
        $this->newLine();

        $this->info('==================================================');
        $this->info('✓ SIMULASI PEMESANAN WHATSAPP BERHASIL!');
        $this->info('==================================================');
        $this->line("1. Akun pelanggan atas nama <comment>{$name}</comment> telah otomatis dibuat.");
        $this->line("2. Stok produk <comment>{$produk->nama_produk}</comment> telah otomatis berkurang.");
        $this->line('3. Pesanan berlabel <fg=green;options=bold>WhatsApp</> dapat dilihat di admin:');
        $this->line('   👉 <comment>http://localhost:8000/admin/pesanan</comment>');
        $this->newLine();

        return self::SUCCESS;
    }
}
