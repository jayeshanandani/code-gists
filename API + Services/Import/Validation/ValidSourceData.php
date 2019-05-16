<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidSourceData
{
    use ImportRepeatedDataTrait;

    public function sourceData($sources)
    {
        $errors = [];
        $success = [];

        foreach ($sources as $source) {
            $checkEmptyColumns = $this->checkEmptyColumn($source);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'SchoolSource headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['source'];
            } else {
                $success[] = $checkEmptyColumns['source'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($sources)
    {
        $existingColumn = ['title'];
        $headers = array_keys($sources);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($source)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($source);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check title details
        if (isKeyExitsAndNullData('title', $source)) {
            $isEmptyColumn = true;
            $source['error']['title'] = $this->requiredMessage('title');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'source' => $source, 'missing_headers' => $checkHeaderData];
    }
}
