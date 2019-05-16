<?php

namespace App\import\Validation;

use App\Masters\State;
use App\Import\ImportRepeatedDataTrait;

class ValidStateData
{
    use ImportRepeatedDataTrait;

    public function stateData($states)
    {
        $errors = [];
        $success = [];
        $countries = $this->countries();
        $statesData = State::select('id', 'name', 'country_id')->get();

        foreach ($states as $state) {
            $checkEmptyColumns = $this->checkEmptyColumn($state, $countries, $statesData);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'state headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['state'];
            } else {
                $success[] = $checkEmptyColumns['state'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($states)
    {
        $existingColumn = ['name', 'country'];
        $headers = array_keys($states);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($state, $countries, $statesData)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($state);
        $isEmptyColumn = (!empty($checkHeaderData));

        $statesArray = [];

        foreach ($statesData as $stateData) {
            $statesArray[$stateData['country_id'] . $stateData['name']] = $stateData['id'];
        }

        // check country_id  details
        if (isKeyExitsAndNotNullData('country', $state) && !keyExists($state['country'], $countries)) {
            $isEmptyColumn = true;
            $state['error']['country'] = 'country details not found.';
        } elseif (isKeyExitsAndNotNullData('country', $state) && keyExists($state['country'], $countries)) {
            $state['country_id'] = $countries[$state['country']];
        }

        if (isKeyExitsAndNotNullData('name', $state) && isKeyExitsAndNotNullData('country', $state) && keyExists($state['country'], $countries)) {
            $key = $countries[$state['country']] . $state['name'];

            if (keyExists($key, $statesArray)) {
                $isEmptyColumn = true;
                $state['error']['name'] = 'State name already exits.';
            }
        }

        //check name details
        if (isKeyExitsAndNullData('name', $state)) {
            $isEmptyColumn = true;
            $state['error']['name'] = $this->requiredMessage('name');
        }

        //check country details
        if (isKeyExitsAndNullData('country', $state)) {
            $isEmptyColumn = true;
            $state['error']['country'] = $this->requiredMessage('country');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'state' => $state, 'missing_headers' => $checkHeaderData];
    }
}
