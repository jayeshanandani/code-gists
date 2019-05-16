<?php

namespace App\Exports\Schools;

use App\Grades\Grade;
use Maatwebsite\Excel\Concerns\FromCollection;

class GradesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Grade::all();
    }
}
