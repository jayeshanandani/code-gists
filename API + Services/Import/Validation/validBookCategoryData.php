<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class validBookCategoryData
{
    use ImportRepeatedDataTrait;

    public function bookCategoryData($bookCategories)
    {
        $errors = [];
        $success = [];

        foreach ($bookCategories as $bookCategory) {
            $checkEmptyColumns = $this->checkEmptyColumn($bookCategory);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Book-category headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['bookCategory'];
            } else {
                $success[] = $checkEmptyColumns['bookCategory'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($bookCategories)
    {
        $existingColumn = ['name'];
        $headers = array_keys($bookCategories);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($bookCategory)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($bookCategory);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check name details
        if (isKeyExitsAndNullData('name', $bookCategory)) {
            $isEmptyColumn = true;
            $bookCategory['error']['name'] = $this->requiredMessage('name');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'bookCategory' => $bookCategory, 'missing_headers' => $checkHeaderData];
    }
}
