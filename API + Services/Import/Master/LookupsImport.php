<?php

namespace App\import\Master;

use App\Masters\Lookup;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class LookupsImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($lookups)
    {
        $result = $this->validate('lookups', $lookups);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $data) {
                $data['uuid'] = generateUuid();
                Lookup::create($data);
            }

            return response()->json(['success' => 'Import Lookups Data successfully'], 200);
        }
    }
}
