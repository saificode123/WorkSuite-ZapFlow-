<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\TravelPayment;
use Yajra\DataTables\Html\Column;

class TravelPaymentDataTable extends BaseDataTable
{
    /**
     * Filter by direction when controller passes a hint through
     * $this->direction (set on receive/make index methods).
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) {
                if ($row->status !== 'posted') {
                    return '<span class="badge badge-secondary">' . e($row->status) . '</span>';
                }
                return '<button class="btn btn-sm btn-outline-danger cancel-payment-btn" data-payment-id="' . $row->id . '">
                            <i class="fa fa-ban"></i>
                        </button>';
            })
            ->addColumn('party_name', fn($row) => $row->partyUser?->name ?? '—')
            ->addColumn('debit_account', fn($row) => $row->debitAccount?->name ?? '—')
            ->addColumn('credit_account', fn($row) => $row->creditAccount?->name ?? '—')
            ->addColumn('booking_group', fn($row) => $row->bookingGroup
                ? e($row->bookingGroup->group_no . ' — ' . $row->bookingGroup->group_name)
                : '—')
            ->editColumn('amount', fn($row) => number_format((float) $row->amount, 2) . ' ' . $row->currency_code)
            ->editColumn('payment_date', fn($row) => $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format(companyOrGlobalSetting()->date_format ?? 'Y-m-d') : '—')
            ->editColumn('status', fn($row) => '<span class="badge badge-' . match ($row->status) {
                'posted'    => 'success',
                'draft'     => 'warning',
                'cancelled' => 'danger',
                default     => 'secondary',
            } . '">' . e($row->status) . '</span>')
            ->editColumn('payment_direction', fn($row) => $row->payment_direction === 'receive'
                ? '<span class="badge badge-success"><i class="fa fa-arrow-down"></i> ' . trans('app.receive') . '</span>'
                : '<span class="badge badge-warning"><i class="fa fa-arrow-up"></i> ' . trans('app.make') . '</span>')
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'status', 'payment_direction']);
    }

    public function query(TravelPayment $model)
    {
        $q = $model->with(['partyUser', 'debitAccount', 'creditAccount', 'bookingGroup']);

        // Optional direction filter (set by controller via $this->direction).
        $direction = null;
        if (isset($this->direction)) {
            $direction = $this->direction;
        } elseif (request()->is('*/travel-payments/receive*')) {
            $direction = 'receive';
        } elseif (request()->is('*/travel-payments/make*')) {
            $direction = 'make';
        }

        if ($direction) {
            $q = $q->where('payment_direction', $direction);
        }

        return $q;
    }

    public function html()
    {
        return $this->setBuilder('travel-payments-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["travel-payments-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
            ]);
    }

    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.direction') => ['data' => 'payment_direction', 'name' => 'payment_direction', 'title' => __('app.direction')],
            __('app.date') => ['data' => 'payment_date', 'name' => 'payment_date', 'title' => __('app.date')],
            __('app.party') => ['data' => 'party_name', 'name' => 'partyUser.name', 'title' => __('app.party')],
            __('modules.payments.debitAccount') => ['data' => 'debit_account', 'name' => 'debitAccount.name', 'title' => __('modules.payments.debitAccount')],
            __('modules.payments.creditAccount') => ['data' => 'credit_account', 'name' => 'creditAccount.name', 'title' => __('modules.payments.creditAccount')],
            __('app.amount') => ['data' => 'amount', 'name' => 'amount', 'title' => __('app.amount')],
            __('modules.payments.method') => ['data' => 'payment_method', 'name' => 'payment_method', 'title' => __('modules.payments.method')],
            __('app.booking') => ['data' => 'booking_group', 'name' => 'bookingGroup.group_no', 'title' => __('app.booking')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
