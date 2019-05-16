<?php

namespace App\Exports\Events;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class EventResourcesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('event_resources')
                        ->leftjoin('events', 'events.id', '=', 'event_resources.event_id')
                        ->leftjoin('schools', 'schools.id', '=', 'event_resources.school_id')
                        ->leftjoin('lookups as event_resource_status', 'event_resource_status.id', '=', 'event_resources.event_resource_status_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
                'schools.name as SCHOOL',
                'events.title as TITLE',
                'event_resources.resource_name as RESOURCE NAME',
                'event_resources.quantity as QUANTITY',
                'event_resources.unit_cost as UNIT COST',
                'event_resources.total_cost as TOTAL COST',
                'event_resources.arrival_date as ARRIVAL DATE',
                'event_resources.phone_no as PHONE NO',
                'event_resource_status.name as EVENT RESOURSE STATUS'
        );
    }
}
