<?php

namespace App\Exports\Users;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class UsersExport implements FromCollection, ShouldAutoSize, WithEvents
{
    use RegistersEventListeners;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return  DB::table('users')
                        ->leftjoin('schools', 'schools.id', '=', 'users.school_id')
                        ->leftjoin('roles', 'roles.id', '=', 'users.role_id')
                        ->leftjoin('districts', 'districts.id', '=', 'users.district_id')
                        ->leftjoin('lookups as gender', 'gender.id', '=', 'users.gender_id')
                        ->leftjoin('lookups as blood_group', 'blood_group.id', '=', 'users.blood_group_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
                'districts.name as DISTRICT',
                'schools.name as SCHOOL',
                'roles.name as ROLE',
                'gender.name as GENDER',
                'blood_group.name as BLOOD GROUP',
                'users.first_name as FIRST NAME',
                'users.middle_name as MIDDLE NAME',
                'users.last_name as LAST NAME',
                'users.username as USER NAME',
                'users.email as EMAIL',
                'users.phone_no as PHONE NO',
                'users.landline_no as LANDLINE NO',
                'users.birthdate as BIRTHDATE',
                'users.height as HEIGHT',
                'users.weight as WEIGHT',
                'users.social_info as SOCIAL INFO',
                'users.other_info as OTHER INFO',
                'users.emergency_contacts as EMERGENCY CONTACTS',
                'users.emergency_contact_name as EMERGENCY NAME',
                'users.emergency_contact_address as EMERGENCY ADDRESS',
                'users.description as DESCRIPTION'
            );
    }
}
