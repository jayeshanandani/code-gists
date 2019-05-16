<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class GradeScalesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('grade_scales')
                        ->leftjoin('schools', 'schools.id', '=', 'grade_scales.school_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL',
            'grade_scales.from_percentage_grade as FROM PERCENTAGE',
            'grade_scales.to_percentage_grade as TO PERCENTAGE',
            'grade_scales.grade_scale as GRADE SCALE'
        );
    }
}
