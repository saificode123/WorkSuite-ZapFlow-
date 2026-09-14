<?php

namespace App\Services;

use App\Models\BookingGroup;
use Illuminate\Support\Facades\DB;

/**
 * TravelReportService
 *
 * Contains calculaton logic for travel-specific financial reports.
 * Separating this from the controller makes the numbers independently testable.
 */
class TravelReportService
{
    /**
     * Compute Umrah-Wise P&L for a single BookingGroup.
     *
     * Revenue  = sum of booking_charges.amount for this group
     *            (these are the customer-facing charges billed to the agent).
     *
     * Cost     = sum of travel_payments.amount where payment_direction = 'make'
     *            and booking_group_id = $bookingGroupId
     *            (these are outbound vendor payments: hotel, transport, visa, etc.)
     *
     * Margin   = Revenue - Cost
     *
     * @param  int $bookingGroupId
     * @return array{revenue: float, cost: float, margin: float}
     */
    public function umrahWisePl(int $bookingGroupId): array
    {
        $revenue = (float) DB::table('booking_charges')
            ->where('booking_group_id', $bookingGroupId)
            ->sum('amount');

        $cost = (float) DB::table('travel_payments')
            ->where('booking_group_id', $bookingGroupId)
            ->where('payment_direction', 'make')
            ->where('status', 'posted')
            ->sum('amount');

        return [
            'revenue' => $revenue,
            'cost'    => $cost,
            'margin'  => $revenue - $cost,
        ];
    }

    /**
     * Return paginated list of BookingGroups with their computed P&L figures.
     * Used by TravelReportController::umrahWisePl() to pass pre-computed data
     * to the view instead of doing arithmetic in Blade.
     *
     * @param  int $perPage
     * @return array  Contains 'items' (array of enriched booking data) and 'paginator'
     */
    public function umrahWisePlList(int $perPage = 20): array
    {
        $paginator = BookingGroup::with(['package', 'passengers'])
            ->orderByDesc('departure_date')
            ->paginate($perPage);

        $items = $paginator->getCollection()->map(function (BookingGroup $bg) {
            $pl = $this->umrahWisePl($bg->id);
            return [
                'booking'  => $bg,
                'revenue'  => $pl['revenue'],
                'cost'     => $pl['cost'],
                'margin'   => $pl['margin'],
            ];
        });

        return [
            'paginator' => $paginator,
            'items'     => $items,
        ];
    }
}
