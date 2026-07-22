<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\Airline;
use Yajra\DataTables\Html\Column;

class AirlineDataTable extends BaseDataTable
{

    private $viewPermission;
    private $editPermission;
    private $deletePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_airline');
        $this->editPermission = user()->permission('edit_airline');
        $this->deletePermission = user()->permission('delete_airline');
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
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('airlines.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if (in_array($this->deletePermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-airline-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check']);
    }

    public function query(Airline $model)
    {
        $model = $model->select('*');

        if ($this->viewPermission == 'added') {
            $model = $model->where('added_by', user()->id);
        }

        return $model;
    }

    public function html()
    {
        return $this->setBuilder('airlines-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["airlines-table"].buttons().container()
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
            __('app.iataCode') => ['data' => 'iata_code', 'name' => 'iata_code', 'title' => __('app.iataCode')],
            __('app.email') => ['data' => 'contact_email', 'name' => 'contact_email', 'title' => __('app.email')],
            __('app.phone') => ['data' => 'contact_phone', 'name' => 'contact_phone', 'title' => __('app.phone')],
            __('app.website') => ['data' => 'website', 'name' => 'website', 'title' => __('app.website')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
