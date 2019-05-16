<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class SubjectsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('subjects')
                        ->leftjoin('schools', 'schools.id', '=', 'subjects.school_id')
                        ->leftjoin('departments', 'departments.id', '=', 'subjects.department_id')
                        ->leftjoin('standard_grades', 'standard_grades.id', '=', 'subjects.standard_grade_id')
                        ->leftjoin('lookups as subjectType', 'subjectType.id', '=', 'subjects.subject_type_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL',
            'departments.name as DEPARTMENT',
            'standard_grades.name as STANDARD GRADE',
            'subjectType.name as STAFF TYPE',
            'subjects.name as SUBJECTS',
            'subjects.description as DESCRIPTION'
    );
    }
}
