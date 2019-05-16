<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidLocationData
{
    use ImportRepeatedDataTrait;

    public function locationData($locations)
    {
        $errors = [];
        $success = [];

        foreach ($locations as $location) {
            $checkEmptyColumns = $this->checkEmptyColumn($location);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'location headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['location'];
            } else {
                $success[] = $checkEmptyColumns['location'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($locations)
    {
        $existingColumn = ['title', 'max_student'];
        $headers = array_keys($locations);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($location)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($location);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check title details
        if (isKeyExitsAndNullData('title', $location)) {
            $isEmptyColumn = true;
            $location['error']['title'] = $this->requiredMessage('title');
        }

        //check max_student details
        if (isKeyExitsAndNullData('max_student', $location)) {
            $isEmptyColumn = true;
            $location['error']['max_student'] = $this->requiredMessage('max_student');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'location' => $location, 'missing_headers' => $checkHeaderData];
    }
}
