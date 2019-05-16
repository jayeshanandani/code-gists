<?php

namespace App\import\Assignments;

use App\Import\BaseImport;
use App\Assignment\Assignment;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class AssignmentsImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($assignments)
    {
        $result = $this->validate('assignments', $assignments);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $assignment) {
                Assignment::create(array_merge($assignment, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import Assignment Data successfully'], 200);
        }
    }
}
