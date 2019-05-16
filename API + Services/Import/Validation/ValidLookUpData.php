<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Import\ImportRepeatedDataTrait;

class ValidLookUpData
{
    use ImportRepeatedDataTrait;

    public function lookupData($lookups)
    {
        $errors = [];
        $success = [];
        $types = Type::pluck('id', 'alias')->all();

        foreach ($lookups as $lookup) {
            $checkEmptyColumns = $this->checkEmptyColumn($lookup, $types);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'lookup headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['lookup'];
            } else {
                $success[] = $checkEmptyColumns['lookup'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($lookups)
    {
        $existingColumn = ['name', 'type'];
        $headers = array_keys($lookups);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($lookup, $types)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($lookup);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check type details exits
        if (isKeyExitsAndNotNullData('type', $lookup) && !keyExists($lookup['type'], $types)) {
            $isEmptyColumn = true;
            $lookup['error']['type'] = 'type details not found.';
        } elseif (isKeyExitsAndNotNullData('type', $lookup) && keyExists($lookup['type'], $types)) {
            $lookup['type_id'] = $types[$lookup['type']];
        }

        //check name details
        if (isKeyExitsAndNullData('name', $lookup)) {
            $isEmptyColumn = true;
            $lookup['error']['name'] = $this->requiredMessage('name');
        }

        //check type details
        if (isKeyExitsAndNullData('type', $lookup)) {
            $isEmptyColumn = true;
            $lookup['error']['type'] = $this->requiredMessage('type');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'lookup' => $lookup, 'missing_headers' => $checkHeaderData];
    }
}
