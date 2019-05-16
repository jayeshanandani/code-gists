<?php

namespace App\import\Events;

use App\Import\ImportInterface;
use App\Events\EventRegistration;
use App\Import\ValidateImportDataTrait;

class EventRegistrationImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($data)
    {
        $result = $this->validate('eventRegistration', $data);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $data) {
                $data['uuid'] = generateUuid();
                EventRegistration::create($data);
            }

            return response()->json(['success' => 'Import Event Registration Data successfully'], 200);
        }
    }
}
