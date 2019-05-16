<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class LecturesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('lectures')
                        ->leftjoin('schools', 'schools.id', '=', 'lectures.school_id')
                        ->leftjoin('users', 'users.id', '=', 'lectures.user_id')
                        ->leftjoin('class_rooms', 'class_rooms.id', '=', 'lectures.class_room_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL',
            'users.first_name as USER NAME',
            'class_rooms.name as CLASSROOMS NAME',
            'lectures.lecture_date as LECTURES DATE',
            'lectures.start_time as START TIME',
            'lectures.end_time as END TIME'
    );
    }
}
