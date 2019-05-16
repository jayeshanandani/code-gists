<?php

namespace App\Exports\Students;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class StudentCredentialsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('students')
                        ->where('is_credential', 1)
                        ->leftjoin('schools', 'schools.id', '=', 'students.school_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL',
            'students.email as EMAIL',
            \DB::raw('concat(students.first_name, " " , students.middle_name, " " , students.last_name) as  NAME'),
            \DB::raw('concat(students.first_name, "@123") as PASSWORD')
        );
    }
}
