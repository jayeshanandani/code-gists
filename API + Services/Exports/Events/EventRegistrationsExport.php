<?php

namespace App\Exports\Events;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class EventRegistrationsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('event_registrations')
                            ->leftjoin('events', 'events.id', '=', 'event_registrations.event_id')
                            ->leftjoin('schools', 'schools.id', '=', 'events.school_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL',
            'events.title as TITLE',
            'event_registrations.email as EMAIL',
            'event_registrations.mobile_no as MOBILE',
            'event_registrations.message as MESSAGE'
        );
    }
}
