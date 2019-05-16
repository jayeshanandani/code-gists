<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidTypeData
{
    use ImportRepeatedDataTrait;

    public function typeData($types)
    {
        $errors = [];
        $success = [];

        foreach ($types as $type) {
            $checkEmptyColumns = $this->checkEmptyColumn($type);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Type headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['type'];
            } else {
                $success[] = $checkEmptyColumns['type'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($types)
    {
        $existingColumn = ['name'];
        $headers = array_keys($types);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($type)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($type);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check name details
        if (isKeyExitsAndNullData('name', $type)) {
            $isEmptyColumn = true;
            $type['error']['name'] = $this->requiredMessage('name');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'type' => $type, 'missing_headers' => $checkHeaderData];
    }
}
