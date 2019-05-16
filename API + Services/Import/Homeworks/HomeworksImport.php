<?php

namespace App\import\Homeworks;

use App\Import\BaseImport;
use App\Homeworks\Homework;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class HomeworksImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($data)
    {
        $result = $this->validate('homeworks', $data);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $data) {
                Homework::create(array_merge($data, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import Homework Data successfully'], 200);
        }
    }
}
