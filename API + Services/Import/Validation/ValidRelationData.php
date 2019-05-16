<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidRelationData
{
    use ImportRepeatedDataTrait;

    public function relationData($relations)
    {
        $errors = [];
        $success = [];

        foreach ($relations as $relation) {
            $checkEmptyColumns = $this->checkEmptyColumn($relation);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'relation headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['relation'];
            } else {
                $success[] = $checkEmptyColumns['relation'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($relations)
    {
        $existingColumn = ['name'];
        $headers = array_keys($relations);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($relation)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($relation);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check name details
        if (isKeyExitsAndNullData('name', $relation)) {
            $isEmptyColumn = true;
            $relation['error']['name'] = $this->requiredMessage('name');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'relation' => $relation, 'missing_headers' => $checkHeaderData];
    }
}
