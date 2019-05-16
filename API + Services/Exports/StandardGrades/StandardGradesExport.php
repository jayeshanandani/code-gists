<?php

namespace App\Exports\StandardGrades;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class StandardGradesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('standard_grades')
                            ->leftjoin('standard_grade_maps', 'standard_grade_maps.standard_grade_id', '=', 'standard_grades.id')
                            ->leftjoin('schools', 'schools.id', '=', 'standard_grade_maps.school_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL',
            'standard_grades.name as STANDARD GRADE',
            'standard_grades.position as POSITION',
            'standard_grades.notes as NOTES'
        );
    }
}
