<?php

namespace App\import\Events;

use App\Import\BaseImport;
use App\Events\EventResource;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class EventResourceImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($data)
    {
        $result = $this->validate('eventResource', $data);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $data) {
                $data['uuid'] = generateUuid();
                EventResource::create($data);
            }

            return response()->json(['success' => 'Import Event Resources Data successfully'], 200);
        }
    }
}
