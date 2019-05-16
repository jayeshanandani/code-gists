<?php

namespace App\import\Schools;

use App\Grades\Grade;
use App\Import\BaseImport;
use App\Import\ImportInterface;

class GradeImport extends BaseImport implements ImportInterface
{
    public function import($grades)
    {
        foreach ($grades as $grade) {
            Grade::create(array_merge($grade, $this->setSchoolIdAndUuid()));
        }

        return response()->json(['success' => 'Import Grade Data successfully'], 200);
    }
}
