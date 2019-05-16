<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class validCountryData
{
    use ImportRepeatedDataTrait;

    public function countryData($countries)
    {
        $errors = [];
        $success = [];
        $countriesData = $this->countries();

        foreach ($countries as $country) {
            $checkEmptyColumns = $this->checkEmptyColumn($country, $countriesData);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Country headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['country'];
            } else {
                $success[] = $checkEmptyColumns['country'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($countries)
    {
        $existingColumn = ['name'];
        $headers = array_keys($countries);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($country, $countriesData)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($country);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check country name details
        if (isKeyExitsAndNotNullData('name', $country) && keyExists($country['name'], $countriesData)) {
            $isEmptyColumn = true;
            $country['error']['name'] = 'Country name already exits.';
        }

        //check name details
        if (isKeyExitsAndNullData('name', $country)) {
            $isEmptyColumn = true;
            $country['error']['name'] = $this->requiredMessage('name');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'country' => $country, 'missing_headers' => $checkHeaderData];
    }
}
