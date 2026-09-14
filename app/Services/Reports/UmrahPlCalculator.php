<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

/**
 * Value object returned by UmrahPlCalculator::calculate().
 *
 * Immutable — callers read revenue, cost, margin directly.
 * Using a typed struct (readonly class) prevents accidental mutation and makes
 * the unit test assertion trivially readable.
 */
readonly class PlResult
{
    public function __construct(
        public readonly float $revenue,
        public readonly float $cost,
        public readonly float $margin,
    ) {}

    /** Convenience: returns the margin as a percentage of revenue (0 if revenue = 0). */
    public function marginPercent(): float
    {
        return $this->revenue > 0
            ? round(($this->margin / $this->revenue) * 100, 2)
            : 0.0;
    }
}

/**
 * UmrahPlCalculator
 *
 * Computes Profit & Loss for a single BookingGroup:
 *
 *   Revenue = SUM(booking_charges.amount) for this group
 *             These are the customer-facing amounts billed to the agent.
 *
 *   Cost    = SUM(travel_payments.amount WHERE payment_direction = 'make' AND status = 'posted')
 *             These are outbound vendor payments: hotel, transport, visa, airline, etc.
 *
 *   Margin  = Revenue − Cost
 *
 * All arithmetic happens in SQL (SUM) — no PHP loops, no Blade-side math.
 * The result is a typed PlResult value object, not a raw array.
 */
class UmrahPlCalculator
{
    /**
     * Compute P&L for one booking group.
     *
     * @param  int $bookingGroupId
     * @return PlResult
     */
    public function calculate(int $bookingGroupId): PlResult
    {
        $revenue = (float) DB::table('booking_charges')
            ->where('booking_group_id', $bookingGroupId)
            ->sum('amount');

        $cost = (float) DB::table('travel_payments')
            ->where('booking_group_id', $bookingGroupId)
            ->where('payment_direction', 'make')
            ->where('status', 'posted')
            ->sum('amount');

        return new PlResult(
            revenue: $revenue,
            cost:    $cost,
            margin:  $revenue - $cost,
        );
    }

    /**
     * Compute P&L for every booking group visible to the current company.
     *
     * Returns an array of ['booking_group_id' => int, 'pl' => PlResult].
     * This is intentionally NOT paginated — callers can slice or paginate
     * the resulting array however they need.
     *
     * @param  int|null $companyId  Filter to a specific company.
     * @return array<array{booking_group_id: int, pl: PlResult}>
     */
    public function calculateAll(?int $companyId = null): array
    {
        // Pull revenue and cost in two aggregated queries (avoids N+1).
        $revenueByGroup = DB::table('booking_charges')
            ->select('booking_group_id', DB::raw('SUM(amount) AS total'))
            ->when($companyId, fn($q) => $q->whereExists(function ($sub) use ($companyId) {
                $sub->select(DB::raw(1))
                    ->from('booking_groups')
                    ->whereColumn('booking_groups.id', 'booking_charges.booking_group_id')
                    ->where('booking_groups.company_id', $companyId);
            }))
            ->groupBy('booking_group_id')
            ->pluck('total', 'booking_group_id');

        $costByGroup = DB::table('travel_payments')
            ->select('booking_group_id', DB::raw('SUM(amount) AS total'))
            ->where('payment_direction', 'make')
            ->where('status', 'posted')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->groupBy('booking_group_id')
            ->pluck('total', 'booking_group_id');

        // Merge both sets of IDs — a group may have revenue but no cost, or vice-versa.
        $allGroupIds = collect($revenueByGroup->keys())
            ->merge($costByGroup->keys())
            ->unique()
            ->values();

        return $allGroupIds->map(function (int $id) use ($revenueByGroup, $costByGroup) {
            $rev  = (float) ($revenueByGroup[$id] ?? 0);
            $cost = (float) ($costByGroup[$id] ?? 0);
            return [
                'booking_group_id' => $id,
                'pl'               => new PlResult(revenue: $rev, cost: $cost, margin: $rev - $cost),
            ];
        })->all();
    }
}
