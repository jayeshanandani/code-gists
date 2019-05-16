<?php

namespace App\import\Schools;

use App\Import\BaseImport;
use App\Schools\ClassRoom;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class ClassImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($classRooms)
    {
        $result = $this->validate('classRoom', $classRooms);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $classRoom) {
                ClassRoom::create(array_merge($classRoom, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import ClassRoom Data successfully.'], 200);
        }
    }
}
