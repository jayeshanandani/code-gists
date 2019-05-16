<?php

namespace App\Exports\Todos;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class TodosExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('todos')
            ->leftjoin('users', 'users.id', '=', 'todos.user_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'users.first_name as FIRST NAME',
            'todos.task_date as TASK DATE',
            'todos.description as DESCRIPTION',
            'todos.status as STATUS'
        );
    }
}
