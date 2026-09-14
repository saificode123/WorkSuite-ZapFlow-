<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\FinancialYear;
use App\Models\JournalVoucher;
use App\Models\JournalVoucherLine;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * DoubleEntryService — enforces debit=credit at the transaction layer.
 *
 * Every posting to the Chart of Accounts must go through this service.
 * Never call JournalVoucher::create() directly from controllers.
 */
class DoubleEntryService
{
    /**
     * Post a balanced journal voucher.
     *
     * @param  array{date: string, narration: string, lines: array<array{account_id: int, debit: float, credit: float}>}  $voucherData
     * @param  int  $createdBy
     * @return JournalVoucher
     *
     * @throws InvalidArgumentException if debit ≠ credit
     * @throws RuntimeException on DB failure
     */
    public function post(array $voucherData, int $createdBy): JournalVoucher
    {
        $this->validateBalance($voucherData['lines']);

        return DB::transaction(function () use ($voucherData, $createdBy) {
            $financialYear = $this->resolveFinancialYear($voucherData['date']);

            $voucherNumber = $voucherData['voucher_number'] ?? ('JV-' . date('Ymd') . '-' . rand(1000, 9999));
            $voucher = JournalVoucher::create([
                'financial_year_id' => $financialYear->id,
                'voucher_number'    => $voucherNumber,
                'date'              => $voucherData['date'],
                'narration'         => $voucherData['narration'] ?? '',
                'created_by'        => $createdBy,
                'company_id'        => company()?->id,
                'is_balanced'       => true,
            ]);


            foreach ($voucherData['lines'] as $line) {
                JournalVoucherLine::create([
                    'journal_voucher_id' => $voucher->id,
                    'account_id'         => $line['account_id'],
                    'debit'              => (float) ($line['debit'] ?? 0),
                    'credit'             => (float) ($line['credit'] ?? 0),
                    'narration'          => $line['narration'] ?? null,
                ]);
            }

            // DB-layer re-verification: sum the actual saved rows
            $this->verifyPostedBalance($voucher);

            return $voucher->fresh(['lines']);
        });
    }

    /**
     * Post a simple two-sided entry (debit one account, credit another).
     */
    public function postSimple(
        string $date,
        int    $debitAccountId,
        int    $creditAccountId,
        float  $amount,
        string $narration,
        int    $createdBy,
        array  $extra = []
    ): JournalVoucher {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be positive.');
        }

        return $this->post([
            'date'     => $date,
            'narration' => $narration,
            'lines'    => [
                array_merge(['account_id' => $debitAccountId,  'debit' => $amount, 'credit' => 0], $extra),
                array_merge(['account_id' => $creditAccountId, 'debit' => 0, 'credit' => $amount], $extra),
            ],
        ], $createdBy);
    }

    /**
     * Validate that total debits equal total credits before hitting the DB.
     *
     * @throws InvalidArgumentException
     */
    public function validateBalance(array $lines): void
    {
        if (empty($lines)) {
            throw new InvalidArgumentException('Journal voucher must have at least two lines.');
        }

        $totalDebit  = round(array_sum(array_column($lines, 'debit')),  2);
        $totalCredit = round(array_sum(array_column($lines, 'credit')), 2);

        if ($totalDebit !== $totalCredit) {
            throw new InvalidArgumentException(
                sprintf(
                    'Journal voucher is unbalanced. Debit: %s, Credit: %s, Difference: %s',
                    number_format($totalDebit, 2),
                    number_format($totalCredit, 2),
                    number_format(abs($totalDebit - $totalCredit), 2)
                )
            );
        }

        if ($totalDebit == 0) {
            throw new InvalidArgumentException('Journal voucher cannot have zero amounts.');
        }

        foreach ($lines as $i => $line) {
            if (!isset($line['account_id']) || !$line['account_id']) {
                throw new InvalidArgumentException("Line #{$i}: account_id is required.");
            }
            if (($line['debit'] ?? 0) < 0 || ($line['credit'] ?? 0) < 0) {
                throw new InvalidArgumentException("Line #{$i}: debit/credit values cannot be negative.");
            }
            if (($line['debit'] ?? 0) > 0 && ($line['credit'] ?? 0) > 0) {
                throw new InvalidArgumentException("Line #{$i}: a line cannot have both debit and credit values.");
            }
        }
    }

    /**
     * Re-verify balance from the database after saving (belt-and-suspenders).
     *
     * @throws RuntimeException
     */
    private function verifyPostedBalance(JournalVoucher $voucher): void
    {
        $sums = JournalVoucherLine::where('journal_voucher_id', $voucher->id)
            ->selectRaw('ROUND(SUM(debit), 2) as total_debit, ROUND(SUM(credit), 2) as total_credit')
            ->first();

        if ((float) $sums->total_debit !== (float) $sums->total_credit) {
            // Roll back is automatic because we're inside DB::transaction()
            throw new RuntimeException(
                'Critical: saved journal voucher is unbalanced. Transaction rolled back.'
            );
        }
    }

    /**
     * Find or throw the active Financial Year for a given date.
     */
    private function resolveFinancialYear(string $date): FinancialYear
    {
        $companyId = company()?->id;
        $query = FinancialYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('is_closed', false);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $fy = $query->first();

        if (!$fy) {
            throw new RuntimeException(
                "No open financial year found for date {$date}. Please configure a Financial Year first."
            );
        }

        return $fy;
    }

    /**
     * Get account balance (debit side = positive for asset/expense, credit for liability/income).
     */
    public function getAccountBalance(int $accountId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $query = JournalVoucherLine::where('account_id', $accountId);

        if ($fromDate) {
            $query->whereHas('journalVoucher', fn($q) => $q->where('date', '>=', $fromDate));
        }
        if ($toDate) {
            $query->whereHas('journalVoucher', fn($q) => $q->where('date', '<=', $toDate));
        }

        $sums = $query->selectRaw('ROUND(SUM(debit), 2) as total_debit, ROUND(SUM(credit), 2) as total_credit')->first();

        $account = ChartOfAccount::find($accountId);

        return [
            'account_id'    => $accountId,
            'account_name'  => $account?->name,
            'account_type'  => $account?->type,
            'total_debit'   => (float) ($sums->total_debit ?? 0),
            'total_credit'  => (float) ($sums->total_credit ?? 0),
            'balance'       => (float) ($sums->total_debit ?? 0) - (float) ($sums->total_credit ?? 0),
        ];
    }
}
