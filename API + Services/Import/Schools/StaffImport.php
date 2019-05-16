<?php

namespace App\import\Schools;

use App\Users\User;
use App\Schools\Staff;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class StaffImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($staffs)
    {
        $result = $this->validate('staff', $staffs);
        $users = $this->users();

        foreach ($result['success'] as $staff) {
            if (!keyExists($staff['email'], $users)) {
                $staff['uuid'] = generateUuid();
                $staff['district_id'] = auth()->user()->district_id;
                $staff['school_id'] = auth()->user()->school_id;
                $staff['role_id'] = config('thunderSchool.roles.school_staff');
                $staffAdmin = User::create($staff);
                $users[$staffAdmin->email] = $staffAdmin->id;

                $staff['uuid'] = generateUuid();
                $staff['school_id'] = auth()->user()->school_id;
                $staff['authorized_user_id'] = $staffAdmin->id;
                $staff['role_id'] = config('thunderSchool.roles.school_staff');
                $staffData = Staff::create($staff);

                $this->importAddress($staff, $staffData->id, Staff::class);
            } else {
                array_push($result['errors'], $staff);
            }
        }

        return response()->json(['success' => 'Import Staff Data successfully', 'error' => $result['errors']], 200);
    }
}
