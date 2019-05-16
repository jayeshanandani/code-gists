<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidMaterialData
{
    use ImportRepeatedDataTrait;

    public function MaterialData($materials)
    {
        $errors = [];
        $success = [];
        $grades = $this->standardGrades();
        $subjects = $this->schoolSubjects();

        foreach ($materials as $material) {
            $checkEmptyColumns = $this->checkEmptyColumn($material, $grades, $subjects);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Material headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['material'];
            } else {
                $success[] = $checkEmptyColumns['material'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($materials)
    {
        $existingColumn = ['title', 'standardGrade', 'subject'];
        $headers = array_keys(($materials));

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($material, $grades, $subjects)
    {
        $isEmptyColumn = false;

        //check Headers
        $checkHeaderData = $this->checkHeaders($material);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check standard_grade_id details
        if (isKeyExitsAndNotNullData('standardGrade', $material) && !keyExists($material['standardGrade'], $grades)) {
            $isEmptyColumn = true;
            $material['error']['standardGrade'] = 'StandardGrade details not found.';
        } elseif (isKeyExitsAndNotNullData('standardGrade', $material) && keyExists($material['standardGrade'], $grades)) {
            $material['standard_grade_id'] = $grades[$material['standardGrade']];
        }

        // check subject_id details
        if (isKeyExitsAndNotNullData('subject', $material) && !keyExists($material['subject'], $subjects)) {
            $isEmptyColumn = true;
            $material['error']['subject'] = 'Subject details not found.';
        } elseif (isKeyExitsAndNotNullData('subject', $material) && keyExists($material['subject'], $subjects)) {
            $material['subject_id'] = $subjects[$material['subject']];
        }

        //check title details
        if (isKeyExitsAndNullData('title', $material)) {
            $isEmptyColumn = true;
            $material['error']['title'] = $this->requiredMessage('title');
        }
        //check standardGrade details
        if (isKeyExitsAndNullData('standardGrade', $material)) {
            $isEmptyColumn = true;
            $material['error']['standardGrade'] = $this->requiredMessage('standardGrade');
        }
        //check subject details
        if (isKeyExitsAndNullData('subject', $material)) {
            $isEmptyColumn = true;
            $material['error']['subject'] = $this->requiredMessage('subject');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'material' => $material, 'missing_headers' => $checkHeaderData];
    }
}
