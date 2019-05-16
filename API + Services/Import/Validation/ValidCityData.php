<?php

namespace App\import\Validation;

use App\Masters\City;
use App\Import\ImportRepeatedDataTrait;

class ValidCityData
{
    use ImportRepeatedDataTrait;

    public function cityData($cities)
    {
        $errors = [];
        $success = [];
        $states = $this->states();
        $citiesData = City::select('id', 'name', 'state_id')->get();

        foreach ($cities as $city) {
            $checkEmptyColumns = $this->checkEmptyColumn($city, $states, $citiesData);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'city headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['city'];
            } else {
                $success[] = $checkEmptyColumns['city'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($cities)
    {
        $existingColumn = ['name', 'state'];
        $headers = array_keys($cities);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($city, $states, $citiesData)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($city);
        $isEmptyColumn = (!empty($checkHeaderData));

        $citiesArray = [];

        foreach ($citiesData as $cityData) {
            $citiesArray[$cityData['state_id'] . $cityData['name']] = $cityData['id'];
        }

        // check state_id  details
        if (isKeyExitsAndNotNullData('state', $city) && !keyExists($city['state'], $states)) {
            $isEmptyColumn = true;
            $city['error']['state'] = 'state details not found.';
        } elseif (isKeyExitsAndNotNullData('state', $city) && keyExists($city['state'], $states)) {
            $city['state_id'] = $states[$city['state']];
        }

        if (isKeyExitsAndNotNullData('name', $city) && isKeyExitsAndNotNullData('state', $city) && keyExists($city['state'], $states)) {
            $key = $states[$city['state']] . $city['name'];

            if (keyExists($key, $citiesArray)) {
                $isEmptyColumn = true;
                $city['error']['name'] = 'city name already exits.';
            }
        }

        //check name details
        if (isKeyExitsAndNullData('name', $city)) {
            $isEmptyColumn = true;
            $city['error']['name'] = $this->requiredMessage('name');
        }

        //check state details
        if (isKeyExitsAndNullData('state', $city)) {
            $isEmptyColumn = true;
            $city['error']['state'] = $this->requiredMessage('state');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'city' => $city, 'missing_headers' => $checkHeaderData];
    }
}
