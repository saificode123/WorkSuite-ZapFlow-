<?php

namespace App\DataTables\Accounts;

use App\Models\FinancialYear;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Column;

class FinancialYearDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();
        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view"><div class="dropdown dropup"><a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="icon-options-vertical icons"></i></a><div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
            if (!$row->is_closed) {
                $action .= '<a class="dropdown-item close-financial-year text-warning" href="javascript:;" data-row-id="' . $row->id . '"><i class="fa fa-lock mr-2"></i>Close Year</a>';
                $action .= '<a href="' . route('financial-years.edit', [$row->id]) . '" class="dropdown-item"><i class="fa fa-edit mr-2"></i>' . __('app.edit') . '</a>';
            }
            $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-row-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '</a>';
            $action .= '</div></div></div>';
            return $action;
        });
        $datatables->editColumn('is_closed', function ($row) {
            return $row->is_closed
                ? '<span class="badge badge-danger">' . __('app.closed') . '</span>'
                : '<span class="badge badge-success">' . __('app.active') . '</span>';
        });
        $datatables->editColumn('start_date', function ($row) {
            return $row->start_date->timezone($this->company->timezone)->translatedFormat($this->company->date_format);
        });
        $datatables->editColumn('end_date', function ($row) {
            return $row->end_date->timezone($this->company->timezone)->translatedFormat($this->company->date_format);
        });
        $datatables->rawColumns(['action', 'is_closed']);
        return $datatables;
    }

    public function query()
    {
        return FinancialYear::where('company_id', company()->id);
    }

    public function html()
    {
        return $this->setBuilder('financial-years-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["financial-years-table"].buttons().container()
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
            __('app.name') => ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            __('modules.accounts.startDate') => ['data' => 'start_date', 'name' => 'start_date', 'title' => __('modules.accounts.startDate')],
            __('modules.accounts.endDate') => ['data' => 'end_date', 'name' => 'end_date', 'title' => __('modules.accounts.endDate')],
            __('app.status') => ['data' => 'is_closed', 'name' => 'is_closed', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
