<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\FinancialYear;
use Illuminate\Http\Request;
use App\DataTables\Accounts\FinancialYearDataTable;

class FinancialYearController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.financialYears';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    public function index(FinancialYearDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_financial_year');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        return $dataTable->render('accounts.financial-years.index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array(user()->permission('add_financial_year'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.addFinancialYear');
        return view('accounts.financial-years.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:financial_years,name,null,id,company_id,' . company()->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        $year = new FinancialYear();
        $year->company_id = company()->id;
        $year->name = $request->name;
        $year->start_date = $request->start_date;
        $year->end_date = $request->end_date;
        $year->save();
        return Reply::success(__('messages.recordSaved'));
    }

    public function edit($id)
    {
        abort_403(!in_array(user()->permission('edit_financial_year'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.editFinancialYear');
        $this->year = FinancialYear::findOrFail($id);
        return view('accounts.financial-years.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:financial_years,name,' . $id . ',id,company_id,' . company()->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        $year = FinancialYear::findOrFail($id);
        $year->name = $request->name;
        $year->start_date = $request->start_date;
        $year->end_date = $request->end_date;
        $year->save();
        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        abort_403(!in_array(user()->permission('delete_financial_year'), ['all', 'added']));
        $year = FinancialYear::where('company_id', company()->id)->findOrFail($id);
        if ($year->journalVouchers()->count() > 0) {
            return Reply::error(__('messages.financialYearHasVouchers'));
        }
        $year->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Close a financial year:
     *  1. Guard against double-close.
     *  2. Compute net P&L (income – expense) from balanced JVs in this year.
     *  3. Roll the net figure into account_openings for the NEXT open year's
     *     retained-earnings account (account type = 'equity').
     *  4. Mark is_closed = true.
     */
    public function close(int $id)
    {
        abort_403(!in_array(user()->permission('edit_financial_year'), ['all', 'added']));

        // Restrict financial year closing strictly to Admin or Accountant
        $allowedRoles = ['admin', 'accountant'];
        $userRoles = method_exists(user(), 'roles') ? user()->roles->pluck('name')->toArray() : user_roles();
        abort_403(!array_intersect($userRoles, $allowedRoles) && !user()->is_superadmin);

        return DB::transaction(function () use ($id) {
            // Pessimistic lock prevents race condition on double-closing
            $year = FinancialYear::where('company_id', company()->id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($year->is_closed) {
                return Reply::error(__('messages.financialYearAlreadyClosed'));
            }

            // Compute net P&L from journal vouchers in this year
            $plRows = DB::table('journal_voucher_lines as jvl')
                ->join('journal_vouchers as jv', 'jv.id', '=', 'jvl.journal_voucher_id')
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'jvl.account_id')
                ->where('jv.financial_year_id', $year->id)
                ->where('jv.is_balanced', 1)
                ->whereIn('coa.type', ['income', 'expense', 'cogs'])
                ->select([
                    'coa.type',
                    DB::raw('SUM(jvl.credit) - SUM(jvl.debit) as net'),
                ])
                ->groupBy('coa.type')
                ->get();

            $netPL = 0.0;
            foreach ($plRows as $row) {
                // income net = credit - debit (positive = profit)
                // expense/cogs net = credit - debit (negative = cost)
                $netPL += (float) $row->net;
            }

            // Find next open financial year for this company
            $nextYear = FinancialYear::where('company_id', company()->id)
                ->where('is_closed', false)
                ->where('id', '!=', $year->id)
                ->where('start_date', '>', $year->end_date)
                ->orderBy('start_date')
                ->lockForUpdate()
                ->first();

            if ($nextYear && abs($netPL) > 0.001) {
                // Find the retained earnings / equity account for this company
                $retainedEarningsAccount = \App\Models\ChartOfAccount::where('company_id', company()->id)
                    ->whereIn('type', ['equity'])
                    ->first();

                if ($retainedEarningsAccount) {
                    $existingOpening = \App\Models\AccountOpening::where([
                        'company_id'       => company()->id,
                        'financial_year_id' => $nextYear->id,
                        'account_id'       => $retainedEarningsAccount->id,
                    ])->lockForUpdate()->first();

                    $currentBalance = $existingOpening ? (float) $existingOpening->opening_balance : 0.0;

                    // Upsert the opening balance for retained earnings in the next year
                    \App\Models\AccountOpening::updateOrCreate(
                        [
                            'company_id'       => company()->id,
                            'financial_year_id' => $nextYear->id,
                            'account_id'       => $retainedEarningsAccount->id,
                        ],
                        [
                            'opening_balance' => $currentBalance + $netPL,
                            'added_by'        => user()->id,
                        ]
                    );
                }
            }

            $year->is_closed = true;
            $year->save();

            // Write to audit_logs for consequential financial write
            \App\Models\AuditLog::create([
                'company_id'  => company()->id,
                'user_id'     => user()->id,
                'module'      => 'accounts',
                'action'      => 'close_financial_year',
                'entity_type' => 'financial_year',
                'entity_id'   => $year->id,
                'field'       => 'is_closed',
                'ip_address'  => request()->ip(),
                'user_agent'  => substr((string) request()->userAgent(), 0, 255),
            ]);

            return Reply::successWithData(
                __('messages.financialYearClosed'),
                ['redirectUrl' => route('financial-years.index')]
            );
        });
    }
}
