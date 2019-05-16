<?php

namespace App\import\Schools;

use App\Schools\Lecture;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class LectureImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($lectures)
    {
        $result = $this->validate('lecture', $lectures);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $lecture) {
                Lecture::create(array_merge($lecture, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import Lecture Data successfully']);
        }
    }
}
