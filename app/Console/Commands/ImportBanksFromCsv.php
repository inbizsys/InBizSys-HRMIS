<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bank;
use App\Models\BankBranch;

class ImportBanksFromCsv extends Command
{
    protected $signature = 'import:banks
                            {--banks= : Path to banks CSV file}
                            {--branches= : Path to bank_branches CSV file}
                            {--fresh : Delete existing data before import}';

    protected $description = 'Import banks and branches from CSV files';

    public function handle(): void
    {
        if ($this->option('fresh')) {
            if ($this->confirm('⚠️  This will DELETE all existing banks and branches. Continue?')) {
                BankBranch::truncate();
                Bank::truncate();
                $this->info('Existing data cleared.');
            } else {
                $this->info('Import cancelled.');
                return;
            }
        }

        // ── Import Banks ──────────────────────────────────────
        $banksFile = $this->option('banks');
        if ($banksFile) {
            if (!file_exists($banksFile)) {
                $this->error("Banks file not found: {$banksFile}");
                return;
            }

            $this->info('Importing banks...');
            $rows     = $this->readCsv($banksFile);
            $imported = 0;
            $skipped  = 0;

            foreach ($rows as $row) {
                $code = strtoupper(trim($row['bank_code'] ?? ''));
                $name = trim($row['bank_name'] ?? '');

                if (!$code || !$name) {
                    $skipped++;
                    continue;
                }

                Bank::updateOrCreate(
                    ['bank_code' => $code],
                    ['bank_name' => $name, 'is_active' => true]
                );
                $imported++;
            }

            $this->info("  ✓ Banks: {$imported} imported, {$skipped} skipped.");
        }

        // ── Import Branches ───────────────────────────────────
        $branchesFile = $this->option('branches');
        if ($branchesFile) {
            if (!file_exists($branchesFile)) {
                $this->error("Branches file not found: {$branchesFile}");
                return;
            }

            $this->info('Importing branches...');
            $rows     = $this->readCsv($branchesFile);
            $imported = 0;
            $skipped  = 0;
            $notFound = [];

            foreach ($rows as $row) {
                $bankCode   = strtoupper(trim($row['bank_code']   ?? ''));
                $branchCode = strtoupper(trim($row['branch_code'] ?? ''));
                $branchName = trim($row['branch_name'] ?? '');

                if (!$bankCode || !$branchCode || !$branchName) {
                    $skipped++;
                    continue;
                }

                $bank = Bank::whereRaw('UPPER(bank_code) = ?', [$bankCode])->first();

                if (!$bank) {
                    $notFound[] = $bankCode;
                    $skipped++;
                    continue;
                }

                BankBranch::updateOrCreate(
                    [
                        'bank_id'     => $bank->id,
                        'branch_code' => $branchCode,
                    ],
                    [
                        'branch_name' => $branchName,
                        'is_active'   => true,
                    ]
                );
                $imported++;
            }

            $this->info("  ✓ Branches: {$imported} imported, {$skipped} skipped.");

            if (!empty($notFound)) {
                $unique = array_unique($notFound);
                $this->warn('  ⚠ Bank codes not found: ' . implode(', ', $unique));
            }
        }

        $this->info('Done!');
    }

    private function readCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        $header = null;

        while (($line = fgetcsv($handle)) !== false) {
            if (!$header) {
                // BOM strip — required for Excel CSV files
                $header = array_map(fn($h) => ltrim(trim($h), "\xEF\xBB\xBF"), $line);
                continue;
            }
            if (count($line) === count($header)) {
                $rows[] = array_combine($header, $line);
            }
        }

        fclose($handle);
        return $rows;
    }
}
