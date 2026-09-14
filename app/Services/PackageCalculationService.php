<?php

namespace App\Services;

use App\Models\Package;
use App\Models\HotelRate;
use App\Models\TransportRate;
use App\Models\VisaCompany;
use App\Models\Discount;
use Illuminate\Support\Facades\DB;

class PackageCalculationService
{
    public function calculate(Package $package): array
    {
        $package->loadMissing(['hotels.rates']);

        $costBreakdown = ['hotel' => [], 'transport' => 0, 'visa' => 0];
        $totalCost = 0;
        $durationDays = $package->duration_days ?? 1;

        $hotelCost = 0;
        foreach ($package->hotels as $hotel) {
            $rate = $hotel->rates()
                ->where('valid_from', '<=', now())
                ->where('valid_to', '>=', now())
                ->orWhere(function ($q) {
                    $q->whereNull('valid_from')->whereNull('valid_to');
                })
                ->orderByDesc('id')
                ->first();

            $nightlyCost = $rate ? (float) $rate->tariff : 0;
            $nightlySell = $rate ? (float) $rate->sell_rate : 0;
            $roomNights = $durationDays - 1;
            $totalHotelCost = $nightlyCost * max($roomNights, 1);
            $totalHotelSell = $nightlySell * max($roomNights, 1);

            $costBreakdown['hotel'][] = [
                'hotel_name' => $hotel->name,
                'city' => $hotel->city,
                'rate_per_night_cost' => $nightlyCost,
                'rate_per_night_sell' => $nightlySell,
                'nights' => max($roomNights, 1),
                'total_cost' => $totalHotelCost,
                'total_sell' => $totalHotelSell,
            ];
            $hotelCost += $nightlyCost * max($roomNights, 1);
            $totalCost += $totalHotelCost;
        }

        // Transport cost calculation via transporter FK
        $transportCost = 0;
        $transportSell = 0;
        if ($package->transporter_id) {
            $tRate = TransportRate::where('transporter_id', $package->transporter_id)
                ->orderByDesc('id')
                ->first();
            if ($tRate) {
                $transportCost = (float) $tRate->rate;
                $transportSell = (float) $tRate->rate;
            }
        }
        $costBreakdown['transport'] = $transportCost;
        $totalCost += $transportCost;

        // Visa cost calculation via visa_company FK
        $visaCost = 0;
        $visaSell = 0;
        if ($package->visa_company_id) {
            $vComp = VisaCompany::find($package->visa_company_id);
            if ($vComp) {
                $visaCost = (float) $vComp->approval_cost_rate;
                $visaSell = (float) ($vComp->approval_sale_rate > 0 ? $vComp->approval_sale_rate : $vComp->approval_cost_rate);
            }
        }
        $costBreakdown['visa'] = $visaCost;
        $totalCost += $visaCost;

        $sellPriceBeforeMarkup = $hotelCost + $transportSell + $visaSell;
        $markupPercent = (float) ($package->markup_percentage ?? 0);
        $markupAmount = $sellPriceBeforeMarkup * ($markupPercent / 100);
        $sellPrice = $sellPriceBeforeMarkup + $markupAmount;

        $discountAmount = 0;
        $discounts = Discount::where(function ($q) use ($package) {
            $q->where('applies_to_type', 'package')
              ->where('applies_to_id', $package->id);
        })->where(function ($q) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
        })->get();

        foreach ($discounts as $discount) {
            if ($discount->discount_type === 'percentage') {
                $discountAmount += $sellPrice * ((float) $discount->value / 100);
            } else {
                $discountAmount += (float) $discount->value;
            }
        }

        $finalPrice = max($sellPrice - $discountAmount, 0);

        return [
            'cost_breakdown' => $costBreakdown,
            'total_cost' => round($totalCost, 2),
            'sell_price_before_markup' => round($sellPriceBeforeMarkup, 2),
            'markup_percentage' => $markupPercent,
            'markup_amount' => round($markupAmount, 2),
            'sell_price' => round($sellPrice, 2),
            'discount_amount' => round($discountAmount, 2),
            'final_price' => round($finalPrice, 2),
        ];
    }

    public function recalculateAndSave(Package $package): Package
    {
        $result = $this->calculate($package);

        $package->update([
            'cost_price' => $result['total_cost'],
            'price' => $result['final_price'],
            'is_price_auto_calculated' => true,
        ]);

        return $package->fresh();
    }
}
