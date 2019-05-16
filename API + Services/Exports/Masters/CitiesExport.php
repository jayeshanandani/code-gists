<?php

namespace App\Exports\Masters;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class CitiesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('cities')
                    ->leftjoin('states', 'states.id', '=', 'cities.state_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'cities.name as CITY',
            'states.name as STATE'
        );
    }
}
