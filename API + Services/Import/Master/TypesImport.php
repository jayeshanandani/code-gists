<?php

namespace App\import\Master;

use App\Masters\Type;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class TypesImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($types)
    {
        $result = $this->validate('type', $types);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $type) {
                $type['uuid'] = generateUuid();
                $type['alias'] = str_slug($type['name'], '_');
                Type::create($type);
            }

            return response()->json(['success' => 'Import Types Data successfully'], 200);
        }
    }
}
