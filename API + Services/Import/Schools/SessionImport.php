<?php

namespace App\import\Schools;

use App\Import\BaseImport;
use App\Schools\SchoolSession;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class SessionImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($sessions)
    {
        $result = $this->validate('session', $sessions);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $session) {
                SchoolSession::create(array_merge($session, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import SchoolSession Data successfully.'], 200);
        }
    }
}
