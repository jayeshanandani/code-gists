<?php

namespace App\import\Schools;

use App\Grades\GradeScale;
use App\Import\BaseImport;
use App\Import\ImportInterface;

class GradeScaleImport extends BaseImport implements ImportInterface
{
    public function import($gradeScales)
    {
        foreach ($gradeScales as $gradeScale) {
            GradeScale::create(array_merge($gradeScale, $this->setSchoolIdAndUuid()));
        }

        return response()->json(['success' => 'Import GradeScale Data successfully'], 200);
    }
}
