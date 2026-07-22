<?php

namespace App\DataTables\Accounts;

use App\Models\ExchangeRate;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Column;

class ExchangeRateDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();
        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view"><div class="dropdown dropup"><a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="icon-options-vertical icons"></i></a><div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
            $action .= '<a href="' . route('exchange-rates.edit', [$row->id]) . '" class="dropdown-item"><i class="fa fa-edit mr-2"></i>' . __('app.edit') . '</a>';
            $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-row-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '</a>';
            $action .= '</div></div></div>';
            return $action;
        });
        $datatables->editColumn('effective_date', function ($row) {
            return $row->effective_date->timezone($this->company->timezone)->translatedFormat($this->company->date_format);
        });
        $datatables->rawColumns(['action']);
        return $datatables;
    }

    public function query()
    {
        return ExchangeRate::where('company_id', company()->id);
    }

    public function html()
    {
        return $this->setBuilder('exchange-rates-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["exchange-rates-table"].buttons().container()
                    .appendTo("#table-actions");
                }',
                'fnDrawCallback' => 'function(oSettings) {
                    $("body").tooltip({selector: \'[data-toggle="tooltip"]\'});
                }',
            ]);
    }

    protected function getColumns()
    {
        return [
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'title' => __('app.id')],
            __('modules.accounts.currencyCode') => ['data' => 'currency_code', 'name' => 'currency_code', 'title' => __('modules.accounts.currencyCode')],
            __('modules.accounts.rateToBase') => ['data' => 'rate_to_base', 'name' => 'rate_to_base', 'title' => __('modules.accounts.rateToBase')],
            __('modules.accounts.effectiveDate') => ['data' => 'effective_date', 'name' => 'effective_date', 'title' => __('modules.accounts.effectiveDate')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
