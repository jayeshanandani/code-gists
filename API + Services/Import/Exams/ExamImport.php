<?php

namespace App\import\Exams;

use App\Exams\Exam;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class ExamImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($exams)
    {
        $result = $this->validate('exam', $exams);

        foreach ($result['success'] as $exam) {
            Exam::create(array_merge($exam, $this->setSchoolIdAndUuid()));
        }

        return response()->json(['success' => 'Import Exam Data successfully', 'error' => $result['errors']], 200);
    }
}
