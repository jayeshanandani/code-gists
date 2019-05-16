<?php

namespace App\import\Students;

use App\Import\ImportInterface;
use App\Students\StudentRelation;
use App\Import\ValidateImportDataTrait;

class StudentRelationImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($relations)
    {
        $result = $this->validate('relation', $relations);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success']  as $relation) {
                $relation['uuid'] = generateUuid();
                StudentRelation::create($relation);
            }

            return response()->json(['success' => 'Import Student Relation Data successfully'], 200);
        }
    }
}
