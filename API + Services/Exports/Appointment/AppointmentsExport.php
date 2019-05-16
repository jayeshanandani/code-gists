<?php

namespace App\Exports\Appointment;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class AppointmentsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('appointments')
                        ->leftJoin('schools', 'schools.id', '=', 'appointments.school_id')
                        ->leftJoin('users', 'users.id', '=', 'appointments.user_id')
                        ->leftJoin('lookups as appointments_type', 'appointments_type.id', '=', 'appointments.type_id')
                        ->leftJoin('lookups as appointment_with', 'appointment_with.id', '=', 'appointments.appointment_with_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'appointments_type.name as APPOINTMENT TYPE',
            'appointments.appointment_date as APPOINTMENT DATE',
            'appointment_with.name as APPOINTMENT WITH',
            'appointments.phone_no as PHONE NO',
            'appointments.description as DESCRIPTION'
        );
    }
}
