<?php

namespace App\import\Users;

use App\Users\User;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class UserImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($users)
    {
        $result = $this->validate('user', $users);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $user) {
                $userData = $this->importUser($user, request('district_id'), request('school_id'));

                $this->importAddress($user, $userData->id, User::class);
            }

            return response()->json(['success' => 'Import Users Data successfully'], 200);
        }
    }
}
