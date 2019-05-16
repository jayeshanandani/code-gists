<?php

namespace App\Exports\Schools;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class LocationsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('locations')
                        ->leftjoin('schools', 'schools.id', '=', 'locations.school_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL NAME',
            'locations.title as LOCATION NAME',
            'locations.description as DESCRIPTION',
            'locations.max_student as MAX STUDENTS'
    );
    }
}
