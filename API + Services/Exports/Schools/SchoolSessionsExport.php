<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class SchoolSessionsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('school_sessions')
                        ->leftjoin('schools', 'schools.id', '=', 'school_sessions.school_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'school_sessions.name as SESSION',
            'school_sessions.start_date as START DATE',
            'school_sessions.end_date as END DATE'
        );
    }
}
