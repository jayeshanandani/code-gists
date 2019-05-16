<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidDepartmentData
{
    use ImportRepeatedDataTrait;

    public function departmentData($departments)
    {
        $errors = [];
        $success = [];

        foreach ($departments as $department) {
            $checkEmptyColumns = $this->checkEmptyColumn($department);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Department headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['department'];
            } else {
                $success[] = $checkEmptyColumns['department'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($departments)
    {
        $existingColumn = ['name', 'message'];
        $headers = array_keys($departments);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($department)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($department);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check name details
        if (isKeyExitsAndNullData('name', $department)) {
            $isEmptyColumn = true;
            $department['error']['name'] = $this->requiredMessage('name');
        }

        //check message details
        if (isKeyExitsAndNullData('message', $department)) {
            $isEmptyColumn = true;
            $department['error']['message'] = $this->requiredMessage('message');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'department' => $department, 'missing_headers' => $checkHeaderData];
    }
}
