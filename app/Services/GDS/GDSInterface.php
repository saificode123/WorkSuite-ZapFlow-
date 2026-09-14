<?php

namespace App\Services\GDS;

/**
 * GDSInterface
 *
 * Defines the contract for all GDS (Global Distribution System) adapters.
 * The default implementation is ManualGDSAdapter which relies on manual PNR
 * entry by a ticketing agent. A third-party GDS (Amadeus, Sabre, etc.) can be
 * wired by implementing this interface and binding it in AppServiceProvider.
 */
interface GDSInterface
{
    /**
     * Search available flights for a given sector and date range.
     *
     * @param  string  $originCode       IATA code of origin airport/city
     * @param  string  $destinationCode  IATA code of destination airport/city
     * @param  string  $departureDate    Y-m-d format
     * @param  int     $passengers       Number of passengers
     * @return array   Array of flight option arrays:
     *                 [ 'flight_no', 'airline_code', 'departure', 'arrival', 'fare', 'class', 'availability' ]
     */
    public function searchFlights(
        string $originCode,
        string $destinationCode,
        string $departureDate,
        int    $passengers = 1
    ): array;

    /**
     * Book a ticket (creates a PNR) on the GDS side.
     *
     * @param  array  $flightData   The flight option selected (from searchFlights result)
     * @param  array  $passengers   Array of passenger data arrays (name, passport, dob, etc.)
     * @param  array  $contactInfo  Booking contact details
     * @return array  [ 'pnr' => string, 'status' => string, 'ticket_numbers' => array, 'fare_total' => float ]
     */
    public function bookTicket(
        array $flightData,
        array $passengers,
        array $contactInfo
    ): array;

    /**
     * Refund / cancel a ticket by PNR.
     *
     * @param  string       $pnr           The PNR to cancel
     * @param  string|null  $ticketNumber  Specific ticket number (null = full PNR cancel)
     * @param  string       $reason        Refund reason code or free-text
     * @return array  [ 'refund_amount' => float, 'status' => string, 'penalty' => float ]
     */
    public function refundTicket(
        string  $pnr,
        ?string $ticketNumber = null,
        string  $reason = 'passenger_request'
    ): array;

    /**
     * Retrieve current status of a PNR from the GDS.
     *
     * @param  string  $pnr  The PNR to query
     * @return array   [ 'pnr' => string, 'status' => string, 'segments' => array, 'passengers' => array ]
     */
    public function getPNRStatus(string $pnr): array;
}
