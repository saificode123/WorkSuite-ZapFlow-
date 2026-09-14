<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\AuditLog;
use App\Models\Passenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Reveal masked sensitive passenger fields (passport_no, birth_date).
 * Every reveal call is written to audit_logs.
 */
class SensitiveFieldController extends AccountBaseController
{
    /**
     * GET /sensitive-fields/reveal
     * Query: ?model=passenger&id=123&field=passport_no
     */
    public function reveal(Request $request)
    {
        $request->validate([
            'model' => 'required|in:passenger',
            'id'    => 'required|integer',
            'field' => 'required|in:passport_no,birth_date',
        ]);

        // Only roles that may view the un-masked value are allowed.
        $allowedRoles = ['admin', 'manager', 'agent'];
        $user = user();
        $userRoles = method_exists($user, 'roles') ? $user->roles->pluck('name')->toArray() : [];
        if (!array_intersect($userRoles, $allowedRoles) && !Gate::allows('view_sensitive_pii')) {
            return Reply::error(__('messages.unAuthorisedUser'));
        }

        $passenger = Passenger::where('company_id', company_id())
            ->findOrFail($request->id);

        $value = $passenger->{$request->field};

        // Log every reveal — PII access must be auditable.
        AuditLog::create([
            'company_id'  => $passenger->company_id,
            'user_id'     => $user->id,
            'module'      => 'passengers',
            'action'      => 'reveal_sensitive_field',
            'entity_type' => 'passenger',
            'entity_id'   => $passenger->id,
            'field'       => $request->field,
            'ip_address'  => $request->ip(),
            'user_agent'  => substr((string) $request->userAgent(), 0, 255),
        ]);

        return Reply::successWithData('', [
            'value' => $value,
        ]);
    }
}
