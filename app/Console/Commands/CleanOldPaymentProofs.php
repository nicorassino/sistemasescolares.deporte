<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanOldPaymentProofs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia comprobantes de pago antiguos para ahorrar espacio.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $threshold = now()->subDays(90);

        $query = Payment::query()
            ->whereNotNull('evidence_file_path')
            ->where(function ($q) use ($threshold) {
                $q->where('updated_at', '<', $threshold)
                    ->orWhere('paid_on_date', '<', $threshold);
            });

        $deletedCount = 0;

        $query->chunkById(100, function ($payments) use (&$deletedCount) {
            foreach ($payments as $payment) {
                $path = $payment->evidence_file_path;
                if ($path && Storage::exists($path)) {
                    Storage::delete($path);
                }

                $payment->update([
                    'evidence_file_path' => null,
                    'evidence_file_size' => null,
                    'evidence_mime_type' => null,
                ]);

                $deletedCount++;
            }
        });

        $this->info('Limpieza completada: ' . $deletedCount . ' comprobantes antiguos eliminados.');

        return Command::SUCCESS;
    }
}
