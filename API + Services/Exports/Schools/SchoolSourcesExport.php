<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class SchoolSourcesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('school_sources')
                        ->leftjoin('schools', 'schools.id', '=', 'school_sources.school_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
                'schools.name as SCHOOL NAME',
                'school_sources.title as  SOURCE TITLE'
    );
    }
}
