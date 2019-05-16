<?php

namespace App\Exports\Events;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class EventsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('events')
                    ->leftjoin('schools', 'schools.id', '=', 'events.school_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL',
            'events.title as TITLE',
            'events.start_date as START DATE',
            'events.end_date as END DATE',
            'events.content as CONTENT'
        );
    }
}
