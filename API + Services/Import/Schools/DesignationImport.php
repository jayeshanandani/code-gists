<?php

namespace App\import\Schools;

use App\Import\BaseImport;
use App\Schools\Designation;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class DesignationImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($designations)
    {
        $result = $this->validate('designation', $designations);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $designation) {
                Designation::create(array_merge($designation, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import Designation Data successfully.'], 200);
        }
    }
}
