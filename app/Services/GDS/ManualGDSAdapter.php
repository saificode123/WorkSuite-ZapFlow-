<?php

namespace App\Services\GDS;

/**
 * ManualGDSAdapter
 *
 * The default GDS adapter. It does NOT connect to any live GDS API.
 * Instead it acts as a pass-through that lets ticketing staff enter PNR
 * numbers and fare details manually (as is standard practice in most
 * small/mid-size travel agencies in Pakistan).
 *
 * To integrate a real GDS (e.g. Amadeus, Sabre, TravelPort):
 *   1. Create a new class implementing GDSInterface (e.g. AmadeusAdapter)
 *   2. Bind it in AppServiceProvider: $this->app->bind(GDSInterface::class, AmadeusAdapter::class)
 *   3. This class can be removed or kept as fallback
 */
class ManualGDSAdapter implements GDSInterface
{
    /**
     * Manual mode: flight search is not supported.
     * Returns empty array — UI should show manual PNR input form instead.
     */
    public function searchFlights(
        string $originCode,
        string $destinationCode,
        string $departureDate,
        int    $passengers = 1
    ): array {
        // Manual adapter does not support live flight search.
        // The UI should present a manual PNR entry form when this returns empty.
        return [];
    }

    /**
     * Manual mode: records the PNR as provided by the agent without calling any API.
     * The $flightData['pnr'] field must be provided by the caller.
     */
    public function bookTicket(
        array $flightData,
        array $passengers,
        array $contactInfo
    ): array {
        $pnr = $flightData['pnr'] ?? ('MAN-' . strtoupper(substr(md5(json_encode($flightData) . microtime()), 0, 8)));

        return [
            'pnr'            => $pnr,
            'status'         => 'confirmed_manual',
            'ticket_numbers' => array_map(fn($p) => 'TKT-' . strtoupper(substr(md5($pnr . ($p['passport_no'] ?? '')), 0, 10)), $passengers),
            'fare_total'     => (float) ($flightData['fare'] ?? 0),
            'adapter'        => 'manual',
            'note'           => 'PNR recorded manually. No GDS API call was made.',
        ];
    }

    /**
     * Manual mode: marks the ticket as refund-pending. Actual refund must be
     * processed externally with the airline and recorded as a TravelPayment (make).
     */
    public function refundTicket(
        string  $pnr,
        ?string $ticketNumber = null,
        string  $reason = 'passenger_request'
    ): array {
        return [
            'refund_amount' => 0.00,
            'status'        => 'refund_pending_manual',
            'penalty'       => 0.00,
            'pnr'           => $pnr,
            'ticket_number' => $ticketNumber,
            'reason'        => $reason,
            'adapter'       => 'manual',
            'note'          => 'Refund must be processed manually with the airline. Record it as a Make Payment.',
        ];
    }

    /**
     * Manual mode: returns a stub PNR status. Actual status must be verified
     * directly with the airline or GDS terminal.
     */
    public function getPNRStatus(string $pnr): array
    {
        return [
            'pnr'        => $pnr,
            'status'     => 'unknown_manual',
            'segments'   => [],
            'passengers' => [],
            'adapter'    => 'manual',
            'note'       => 'Manual adapter: check PNR status directly with airline/GDS terminal.',
        ];
    }
}
