<?php

namespace App\Exports\Homework;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class HomeworksExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('homeworks')
                        ->leftjoin('schools', 'schools.id', '=', 'homeworks.school_id')
                        ->leftjoin('users', 'users.id', '=', 'homeworks.user_id')
                        ->leftjoin('class_rooms', 'class_rooms.id', '=', 'homeworks.class_room_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'homeworks.active as ACTIVE',
            'schools.name as SCHOOL',
            'users.first_name as USER',
            'class_rooms.name as CLASS',
            'homeworks.title as TITLE',
            'homeworks.description as DESCRIPTION',
            'homeworks.due_date as DUE DATE',
            'homeworks.start_time as START TIME',
            'homeworks.end_time as END TIME'
        );
    }
}
