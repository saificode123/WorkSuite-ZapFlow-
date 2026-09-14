<?php

namespace App\Services;

use App\Models\BookingGroup;
use App\Models\Passenger;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

/**
 * Sends booking-related SMS via Twilio.
 *
 * Reads the company's Twilio credentials from company_settings (fallback to
 * global config). If credentials are missing or the Twilio API fails, the
 * service degrades gracefully — failures are logged but never throw, so a
 * missing SMS never blocks the core booking flow.
 */
class TravelSmsService
{
    protected ?Client $client = null;
    protected ?string $from = null;
    protected bool $enabled = false;

    public function __construct()
    {
        $this->bootstrap();
    }

    protected function bootstrap(): void
    {
        $sid   = config('services.twilio.sid')   ?? env('TWILIO_SID');
        $token = config('services.twilio.token') ?? env('TWILIO_AUTH_TOKEN');
        $from  = config('services.twilio.from')  ?? env('TWILIO_FROM');

        if (!$sid || !$token || !$from) {
            $this->enabled = false;
            return;
        }

        try {
            $this->client = new Client($sid, $token);
            $this->from   = $from;
            $this->enabled = true;
        } catch (\Throwable $e) {
            Log::warning('Twilio init failed: ' . $e->getMessage());
            $this->enabled = false;
        }
    }

    /**
     * Send an SMS to a single number. Returns true on success, false on failure.
     */
    public function send(string $to, string $body): bool
    {
        if (!$this->enabled) {
            Log::info('Twilio disabled — would have sent SMS', compact('to', 'body'));
            return false;
        }

        try {
            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $body,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Twilio send failed: ' . $e->getMessage(), compact('to'));
            return false;
        }
    }

    /**
     * Notify the group leader that a payment was received.
     */
    public function notifyPaymentReceived(BookingGroup $booking, float $amount, string $currency): bool
    {
        $phone = $this->resolveGroupLeaderPhone($booking);
        if (!$phone) return false;

        $body = "Payment of {$currency} {$amount} received for booking '{$booking->group_name}'. Thank you. – ZapFlow";

        return $this->send($phone, $body);
    }

    /**
     * Notify the group leader that a visa status changed.
     */
    public function notifyVisaStatusChanged(Passenger $passenger, string $newStatus): bool
    {
        $phone = $this->resolvePassengerPhone($passenger);
        if (!$phone) return false;

        $readable = ucwords(str_replace('_', ' ', $newStatus));
        $body = "Visa update for {$passenger->first_name}: status is now '{$readable}'. – ZapFlow";

        return $this->send($phone, $body);
    }

    /**
     * Notify the passenger that a voucher has been issued.
     */
    public function notifyVoucherIssued(Passenger $passenger, string $voucherNumber): bool
    {
        $phone = $this->resolvePassengerPhone($passenger);
        if (!$phone) return false;

        $body = "Your voucher #{$voucherNumber} is ready. Present it at the hotel. – ZapFlow";

        return $this->send($phone, $body);
    }

    protected function resolveGroupLeaderPhone(BookingGroup $booking): ?string
    {
        if (!$booking->group_leader_phone) return null;
        return $this->normalizePhone($booking->group_leader_phone);
    }

    protected function resolvePassengerPhone(Passenger $passenger): ?string
    {
        if (!$passenger->mobile_no) return null;
        return $this->normalizePhone($passenger->mobile_no);
    }

    /**
     * Normalize phone to E.164 (+CountryCode...). Defaults to + if missing.
     */
    protected function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);
        if (!$phone) return null;
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . ltrim($phone, '0');
        }
        return $phone;
    }
}
