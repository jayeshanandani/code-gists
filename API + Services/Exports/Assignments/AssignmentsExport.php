<?php

namespace App\Exports\Assignments;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class AssignmentsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('assignments')
                        ->leftjoin('schools', 'schools.id', '=', 'assignments.school_id')
                        ->leftjoin('users', 'users.id', '=', 'assignments.user_id')
                        ->leftjoin('class_rooms', 'class_rooms.id', '=', 'assignments.class_room_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL',
            'users.first_name as USER',
            'class_rooms.name as CLASS',
            'assignments.title as TITLE',
            'assignments.description as DESCRIPTION',
            'assignments.due_date as DUE DATE',
            'assignments.visible_date as VISIBLE DATE'

        );
    }
}
