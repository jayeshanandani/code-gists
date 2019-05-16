<?php

namespace App\Exports\Masters;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class DistrictsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('districts')
                        ->leftjoin('users', 'users.id', '=', 'districts.authorized_user_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
                'districts.name as NAME',
                'districts.phone_no as PHONE NO',
                'districts.landline_no as LANDLINE NO',
                'districts.fax_no as FAX NO',
                'users.first_name as AUTHORIZED USER'
        );
    }
}
