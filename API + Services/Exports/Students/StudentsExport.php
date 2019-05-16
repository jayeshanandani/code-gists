<?php

namespace App\Exports\Students;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class StudentsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('students')
                        ->leftjoin('schools', 'schools.id', '=', 'students.school_id')
                        ->leftjoin('lookups as gender', 'gender.id', '=', 'students.gender_id')
                        ->leftjoin('standard_grades', 'standard_grades.id', '=', 'students.standard_grade_id')
                        ->leftjoin('school_sessions', 'school_sessions.id', '=', 'students.session_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL',
            'gender.name as GENDER',
            'standard_grades.name as STANDARD GRADE',
            'school_sessions.name as SESSION',
            'students.first_name as FIRST NAME',
            'students.last_name as LAST NAME',
            'students.middle_name as MIDDLE NAME',
            'students.birthdate as BIRTHDATE',
            'students.email as EMAIL',
            'students.phone_no as PHONE NO',
            'students.mobile_no as MOBILE NO',
            'students.remarks as REMARKS',
            'students.admission_inquiry_id as ADMISSION INQUIRY',
            'students.comments as STUDENT COMMENTS',
            'students.previous_academic_details as PREVIOUS ACEDEMIC DETAILS'
        );
    }
}
