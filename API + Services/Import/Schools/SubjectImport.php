<?php

namespace App\import\Schools;

use App\Schools\Subject;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class SubjectImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($subjects)
    {
        $result = $this->validate('subject', $subjects);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as  $subject) {
                Subject::create(array_merge($subject, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import Subject Data successfully'], 200);
        }
    }
}
