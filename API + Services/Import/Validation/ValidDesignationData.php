<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidDesignationData
{
    use ImportRepeatedDataTrait;

    public function designationData($designations)
    {
        $errors = [];
        $success = [];

        foreach ($designations as $designation) {
            $checkEmptyColumns = $this->checkEmptyColumn($designation);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Designation headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['designation'];
            } else {
                $success[] = $checkEmptyColumns['designation'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($designations)
    {
        $existingColumn = ['name'];
        $headers = array_keys($designations);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($designation)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($designation);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check name details
        if (isKeyExitsAndNullData('name', $designation)) {
            $isEmptyColumn = true;
            $designation['error']['name'] = $this->requiredMessage('name');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'designation' => $designation, 'missing_headers' => $checkHeaderData];
    }
}
