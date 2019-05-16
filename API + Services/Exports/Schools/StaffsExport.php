<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class StaffsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('staff')
                            ->leftjoin('users', 'users.id', '=', 'staff.authorized_user_id')
                            ->leftjoin('schools', 'schools.id', '=', 'staff.school_id')
                            ->leftjoin('departments', 'departments.id', '=', 'staff.department_id')
                            ->leftjoin('school_sessions', 'school_sessions.id', '=', 'staff.session_id')
                            ->leftjoin('designations', 'designations.id', '=', 'staff.designation_id')
                            ->leftjoin('roles', 'roles.id', '=', 'staff.role_id')
                            ->leftjoin('lookups as staffType', 'staffType.id', '=', 'staff.staff_type_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'users.first_name as STAFF',
            'schools.name as SCHOOL',
            'departments.name as DEPARTMENT',
            'school_sessions.name as SESSION',
            'designations.name as DESIGNATION',
            'roles.name as ROLE',
            'staffType.name as STAFF TYPE',
            'staff.hire_date as HIRE DATE'
    );
    }
}
