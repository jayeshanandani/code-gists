<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class validStandardGradeData
{
    use ImportRepeatedDataTrait;

    public function standardGradeData($grades)
    {
        $errors = [];
        $success = [];

        foreach ($grades as $grade) {
            $checkEmptyColumns = $this->checkEmptyColumn($grade);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'StandardGrade headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['grade'];
            } else {
                $success[] = $checkEmptyColumns['grade'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($grades)
    {
        $existingColumn = ['name', 'position'];
        $headers = array_keys($grades);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($grade)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($grade);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check name details
        if (isKeyExitsAndNullData('name', $grade)) {
            $isEmptyColumn = true;
            $grade['error']['name'] = $this->requiredMessage('name');
        }

        //check position details
        if (isKeyExitsAndNullData('position', $grade)) {
            $isEmptyColumn = true;
            $grade['error']['position'] = $this->requiredMessage('position');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'grade' => $grade, 'missing_headers' => $checkHeaderData];
    }
}
