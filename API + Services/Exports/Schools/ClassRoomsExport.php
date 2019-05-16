<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class ClassRoomsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('class_rooms')
                            ->leftjoin('schools', 'schools.id', '=', 'class_rooms.school_id')
                            ->leftjoin('users', 'users.id', '=', 'class_rooms.user_id')
                            ->leftjoin('locations', 'locations.id', '=', 'class_rooms.location_id')
                            ->leftjoin('standard_grades', 'standard_grades.id', '=', 'class_rooms.standard_grade_id')
                            ->leftjoin('school_sessions', 'school_sessions.id', '=', 'class_rooms.session_id')
                            ->leftjoin('subjects', 'subjects.id', '=', 'class_rooms.subject_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
                'schools.name as SCHOOL NAME',
                'class_rooms.name as CLASS',
                'users.first_name as USER',
                'locations.title as LOCATION',
                'standard_grades.name as STANDARD GRADE',
                'school_sessions.name as SESSION',
                'subjects.name as SUBJECTS NAME',
                'class_rooms.max_student as MAX STUDENTS',
                'class_rooms.class_days as CLASS DAY',
                'class_rooms.start_time as START TIME',
                'class_rooms.end_time as END TIME'
        );
    }
}
