<?php

namespace App\import\Master;

use App\Masters\Country;
use App\Import\ImportInterface;
use App\Import\ImportRepeatedDataTrait;
use App\Import\ValidateImportDataTrait;

class CountriesImport implements ImportInterface
{
    use ImportRepeatedDataTrait, ValidateImportDataTrait;

    public function import($countriesData)
    {
        $errors = [];
        $countries = $this->countries();
        $result = $this->validate('country', $countriesData);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $country) {
                $country['uuid'] = generateUuid();
                $countries = Country::create($country);

                $countries[$countries->name] = $countries->id;
            }

            return response()->json(['success' => 'Import Countries Data successfully'], 200);
        }
    }
}
