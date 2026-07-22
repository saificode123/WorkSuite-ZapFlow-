<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\Discount;
use Yajra\DataTables\Html\Column;

class DiscountDataTable extends BaseDataTable
{

    private $viewPermission;
    private $editPermission;
    private $deletePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_discount');
        $this->editPermission = user()->permission('edit_discount');
        $this->deletePermission = user()->permission('delete_discount');
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if (in_array($this->editPermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('discounts.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if (in_array($this->deletePermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-discount-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('type', function ($row) {
                return $row->type == 'percentage' ? __('app.percentage') : __('app.fixed');
            })
            ->editColumn('value', function ($row) {
                return $row->type == 'percentage' ? $row->value . '%' : currency_format($row->value, company()->currency_id);
            })
            ->editColumn('is_active', function ($row) {
                if ($row->is_active) {
                    return '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.yes');
                }
                return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.no');
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'is_active']);
    }

    public function query(Discount $model)
    {
        $model = $model->select('*');

        if ($this->viewPermission == 'added') {
            $model = $model->where('added_by', user()->id);
        }

        return $model;
    }

    public function html()
    {
        return $this->setBuilder('discounts-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["discounts-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
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
            __('app.name') => ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            __('app.type') => ['data' => 'type', 'name' => 'type', 'title' => __('app.type')],
            __('app.value') => ['data' => 'value', 'name' => 'value', 'title' => __('app.value')],
            __('app.active') => ['data' => 'is_active', 'name' => 'is_active', 'title' => __('app.active')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
