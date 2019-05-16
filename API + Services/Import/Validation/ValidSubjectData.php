<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidSubjectData
{
    use ImportRepeatedDataTrait;

    public function SubjectData($subjects)
    {
        $errors = [];
        $success = [];
        $grades = $this->standardGrades();
        $departments = $this->schoolDepartments();
        $subjectType = $this->subjectTypes();

        foreach ($subjects as $subject) {
            $checkEmptyColumns = $this->checkEmptyColumn($subject, $grades, $departments, $subjectType);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Subject headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['subject'];
            } else {
                $success[] = $checkEmptyColumns['subject'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($subjects)
    {
        $existingColumn = ['name', 'standardGrade', 'department', 'subject_type'];
        $headers = array_keys($subjects);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($subject, $grades, $departments, $subjectType)
    {
        $isEmptyColumn = false;

        //check Headers
        $checkHeaderData = $this->checkHeaders($subject);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check standard_grade_id details
        if (isKeyExitsAndNotNullData('standardGrade', $subject) && !keyExists($subject['standardGrade'], $grades)) {
            $isEmptyColumn = true;
            $subject['error']['standardGrade'] = 'StandardGrade details not found.';
        } elseif (isKeyExitsAndNotNullData('standardGrade', $subject) && keyExists($subject['standardGrade'], $grades)) {
            $subject['standard_grade_id'] = $grades[$subject['standardGrade']];
        }

        //check department_id details
        if (isKeyExitsAndNotNullData('department', $subject) && !keyExists($subject['department'], $departments)) {
            $isEmptyColumn = true;
            $subject['error']['department'] = 'Department details not found.';
        } elseif (isKeyExitsAndNotNullData('department', $subject) && keyExists($subject['department'], $departments)) {
            $subject['department_id'] = $departments[$subject['department']];
        }

        //check department_id details
        if (isKeyExitsAndNotNullData('subject_type', $subject) && !keyExists($subject['subject_type'], $subjectType)) {
            $isEmptyColumn = true;
            $subject['error']['subject_type'] = 'Subject_type details not found.';
        } elseif (isKeyExitsAndNotNullData('subject_type', $subject) && keyExists($subject['subject_type'], $subjectType)) {
            $subject['subject_type_id'] = $subjectType[$subject['subject_type']];
        }

        //check name details
        if (isKeyExitsAndNullData('name', $subject)) {
            $isEmptyColumn = true;
            $subject['error']['name'] = $this->requiredMessage('name');
        }
        //check standardGrade details
        if (isKeyExitsAndNullData('standardGrade', $subject)) {
            $isEmptyColumn = true;
            $subject['error']['standardGrade'] = $this->requiredMessage('standardGrade');
        }
        //check department details
        if (isKeyExitsAndNullData('department', $subject)) {
            $isEmptyColumn = true;
            $subject['error']['department'] = $this->requiredMessage('department');
        }
        //check subject_type details
        if (isKeyExitsAndNullData('subject_type', $subject)) {
            $isEmptyColumn = true;
            $subject['error']['subject_type'] = $this->requiredMessage('subject_type');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'subject' => $subject, 'missing_headers' => $checkHeaderData];
    }
}
