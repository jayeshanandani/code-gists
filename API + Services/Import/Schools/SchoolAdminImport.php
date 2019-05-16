<?php

namespace App\import\Schools;

use App\Users\User;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class SchoolAdminImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    /**
     * Get SchoolId.
     */
    public function getSchoolId()
    {
        $currentUser = auth()->user();

        if ($currentUser->role_id == config('thunderSchool.roles.district_admin')) {
            return request('school_id');
        } elseif ($currentUser->role_id === config('thunderSchool.roles.school_admin')) {
            return auth()->user()->school_id;
        }
    }

    public function import($schoolAdmins)
    {
        $result = $this->validate('user', $schoolAdmins);
        $user = $this->users();
        $schoolId = $this->getSchoolId();

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $schoolAdmin) {
                if (!keyExists($schoolAdmin['email'], $user)) {
                    $schoolAdmins = $this->importUser($schoolAdmin, auth()->user()->district_id, $schoolId);
                    $user[$schoolAdmins->email] = $schoolAdmins->id;

                    $this->importAddress($schoolAdmin, $schoolAdmins->id, User::class);
                } else {
                    array_push($result['errors'], $schoolAdmin);
                }
            }

            return response()->json(['success' => 'Import SchoolAdmin Data successfully.'], 200);
        }
    }
}
