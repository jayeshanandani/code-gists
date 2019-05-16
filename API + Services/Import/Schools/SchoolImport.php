<?php

namespace App\import\Schools;

use App\Schools\School;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class SchoolImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($schools)
    {
        $result = $this->validate('school', $schools);
        $users = $this->users();

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $school) {
                if (!keyExists($school['email'], $users)) {
                    $school['uuid'] = generateUuid();
                    $school['district_id'] = auth()->user()->district_id;
                    $schoolData = School::create($school);

                    $schoolAdmin = $this->importUser($school, auth()->user()->district_id, $schoolData->id);

                    $schoolData->update(['authorized_user_id' => $schoolAdmin->id]);
                    $users[$schoolAdmin->email] = $schoolAdmin->id;

                    $address = $this->importAddress($school, $schoolData->id, School::class);
                } else {
                    array_push($result['errors'], $school);
                }
            }

            return response()->json(['success' => 'Import School Data successfully.'], 200);
        }
    }
}
