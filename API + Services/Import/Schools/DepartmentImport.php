<?php

namespace App\import\Schools;

use App\Import\BaseImport;
use App\Schools\Department;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class DepartmentImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($departments)
    {
        $result = $this->validate('department', $departments);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $department) {
                Department::create(array_merge($department, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import Departments Data successfully.'], 200);
        }
    }
}
