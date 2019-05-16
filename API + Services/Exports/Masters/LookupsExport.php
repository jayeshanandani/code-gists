<?php

namespace App\Exports\Masters;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class LookupsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('lookups')
                        ->leftjoin('types', 'types.id', '=', 'lookups.type_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'types.name as TYPES',
            'lookups.name as LOOKUPS'
        );
    }
}
