<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class MaterialsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('materials')
                        ->leftjoin('schools', 'schools.id', '=', 'materials.school_id')
                        ->leftjoin('subjects', 'subjects.id', '=', 'materials.subject_id')
                        ->leftjoin('standard_grades', 'standard_grades.id', '=', 'materials.standard_grade_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
                'schools.name as SCHOOL',
                'subjects.name as SUBJECT',
                'materials.title as TITLE',
                'materials.description as DESCRIPTION',
                'materials.due_date as DUE DATE',
                'materials.publisher_name as PUBLISHER',
                'materials.isbn_number as ISBN NUMBER',
                'materials.author_name as AUTHOR'
    );
    }
}
