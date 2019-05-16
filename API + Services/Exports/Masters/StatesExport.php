<?php

namespace App\Exports\Masters;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class StatesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('states')
                            ->leftjoin('countries', 'countries.id', '=', 'states.country_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'states.name as STATES',
            'countries.name as COUNTRIES'
        );
    }
}
