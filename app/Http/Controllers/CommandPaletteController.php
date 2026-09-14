<?php

namespace App\Http\Controllers;

use App\Models\BookingGroup;
use App\Models\Passenger;
use App\Models\Voucher;
use App\Models\User;
use App\Models\ClientDetails;
use App\Models\Hotel;
use App\Models\Package;
use App\Helper\Reply;
use Illuminate\Http\Request;

class CommandPaletteController extends AccountBaseController
{
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return Reply::successWithData('', ['results' => []]);
        }

        $limit = 10;
        $results = [];

        $bookings = BookingGroup::where('company_id', company_id())
            ->where(function ($query) use ($q) {
                $query->where('group_name', 'like', "%{$q}%")
                    ->orWhere('group_no', 'like', "%{$q}%");
            })
            ->limit($limit)
            ->get()
            ->map(fn($b) => [
                'type' => 'booking',
                'label' => $b->group_name,
                'sub' => $b->group_no ?? 'Booking',
                'url' => route('bookings.show', $b->id),
                'icon' => 'fa-users',
            ]);

        $passengers = Passenger::whereHas('bookingGroup', fn($qry) => $qry->where('company_id', company_id()))
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('family_name', 'like', "%{$q}%")
                    ->orWhere('passport_no', 'like', "%{$q}%");
            })
            ->with('bookingGroup')
            ->limit($limit)
            ->get()
            ->map(fn($p) => [
                'type' => 'passenger',
                'label' => $p->full_name,
                'sub' => $p->passport_no . ' | ' . ($p->bookingGroup?->group_name ?? ''),
                'url' => route('bookings.show', $p->booking_group_id),
                'icon' => 'fa-user',
            ]);

        $vouchers = Voucher::where('company_id', company_id())
            ->where('voucher_number', 'like', "%{$q}%")
            ->with('bookingGroup')
            ->limit($limit)
            ->get()
            ->map(fn($v) => [
                'type' => 'voucher',
                'label' => $v->voucher_number,
                'sub' => ($v->bookingGroup?->group_name ?? '') . ' — ' . ucfirst($v->status),
                'url' => route('vouchers.show', $v->id),
                'icon' => 'fa-file-alt',
            ]);

        $customers = User::where('company_id', company_id())
            ->where('login', 'client')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit($limit)
            ->get()
            ->map(fn($u) => [
                'type' => 'customer',
                'label' => $u->name,
                'sub' => $u->email,
                'url' => route('clients.show', $u->id),
                'icon' => 'fa-briefcase',
            ]);

        $hotels = collect();
        if (\Schema::hasTable('hotels')) {
            $hotels = Hotel::where('company_id', company_id())
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%");
                })
                ->limit($limit)
                ->get()
                ->map(fn($h) => [
                    'type' => 'hotel',
                    'label' => $h->name,
                    'sub' => $h->city,
                    'url' => route('hotels.index'),
                    'icon' => 'fa-hotel',
                ]);
        }

        $packages = collect();
        if (\Schema::hasTable('packages')) {
            $packages = Package::where('company_id', company_id())
                ->where('name', 'like', "%{$q}%")
                ->limit($limit)
                ->get()
                ->map(fn($p) => [
                    'type' => 'package',
                    'label' => $p->name,
                    'sub' => ($p->duration_days ?? '') . ' days',
                    'url' => route('packages.index'),
                    'icon' => 'fa-box',
                ]);
        }

        $results = array_merge(
            $bookings->toArray(),
            $passengers->toArray(),
            $vouchers->toArray(),
            $customers->toArray(),
            $hotels->toArray(),
            $packages->toArray(),
        );

        usort($results, fn($a, $b) => strcasecmp($a['label'], $b['label']));

        return Reply::successWithData('', ['results' => array_slice($results, 0, 20)]);
    }
}
