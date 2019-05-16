<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Import\ImportRepeatedDataTrait;

class ValidStaffData
{
    use ImportRepeatedDataTrait;

    public function StaffData($staffs)
    {
        $errors = [];
        $success = [];
        $sessions = $this->schoolSessions();
        $departments = $this->schoolDepartments();
        $designations = $this->designations();
        $cities = $this->cities();
        $states = $this->states();
        $countries = $this->countries();
        $users = $this->users();

        $validType = true;
        $types = Type::whereIn('alias', ['gender', 'blood_group', 'staff_type'])->with('lookups')->get()->groupBy('alias')->all();

        if (!$types['gender']) {
            $validType = false;
        } else {
            $genders = $types['gender']->first()->lookups->pluck('id', 'name')->all();
        }
        if (!$types['blood_group']) {
            $validType = false;
        } else {
            $bloodGroups = $types['blood_group']->first()->lookups->pluck('id', 'name')->all();
        }
        if (!$types['staff_type']) {
            $validType = false;
        } else {
            $staffType = $types['staff_type']->first()->lookups->pluck('id', 'name')->all();
        }

        foreach ($staffs as $staff) {
            if (!keyExists($staff['session'], $sessions) ||
                !keyExists($staff['department'], $departments) ||
                !keyExists($staff['designation'], $designations) ||
                !keyExists($staff['city'], $cities) ||
                !keyExists($staff['state'], $states) ||
                !keyExists($staff['country'], $countries) ||
                keyExists($staff['email'], $users) ||
                $validType == false) {
                $errors[] = $staff;
            } else {
                $staff['department_id'] = $departments[$staff['department']];
                $staff['session_id'] = $sessions[$staff['session']];
                $staff['designation_id'] = $designations[$staff['designation']];
                $staff['gender_id'] = $genders[$staff['gender']];
                $staff['blood_group_id'] = $bloodGroups[$staff['bloodGroup']];
                $staff['staff_type_id'] = $staffType[$staff['staff_type']];
                $staff['city_id'] = $cities[$staff['city']];
                $staff['state_id'] = $states[$staff['state']];
                $staff['country_id'] = $countries[$staff['country']];
                $success[] = $staff;
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }
}
