<?php

namespace App\import\Schools;

use App\Schools\Location;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class LocationImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($locations)
    {
        $result = $this->validate('location', $locations);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $location) {
                Location::create(array_merge($location, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import Location Data successfully.'], 200);
        }
    }
}
