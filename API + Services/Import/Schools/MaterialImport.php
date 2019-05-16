<?php

namespace App\import\Schools;

use App\Import\BaseImport;
use App\Materials\Material;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class MaterialImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($materials)
    {
        $result = $this->validate('material', $materials);
        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as  $material) {
                Material::create(array_merge($material, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import Material Data successfully'], 200);
        }
    }
}
