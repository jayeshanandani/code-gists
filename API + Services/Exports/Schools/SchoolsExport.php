<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class SchoolsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('schools')
                        ->leftjoin('districts', 'districts.id', '=', 'schools.district_id')
                        ->leftjoin('users', 'users.id', '=', 'schools.authorized_user_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL NAME',
            'districts.name as DISTRICT NAME',
            'schools.phone_no as PHONE NO',
            'schools.landline_no as LANDLINE NO',
            'schools.fax_no as FAX NO',
            'schools.max_student as MAX STUDENTS',
            'users.first_name as AUTORIZED USER NAME'
    );
    }
}
