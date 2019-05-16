<?php

namespace App\import\Master;

use App\Masters\City;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class CitiesImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($citiesData)
    {
        $result = $this->validate('city', $citiesData);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $city) {
                $city['uuid'] = generateUuid();
                $cities = City::create($city);
            }

            return response()->json(['success' => 'Import Cities Data successfully'], 200);
        }
    }
}
