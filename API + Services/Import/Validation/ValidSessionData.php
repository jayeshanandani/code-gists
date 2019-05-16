<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidSessionData
{
    use ImportRepeatedDataTrait;

    public function sessionData($sessions)
    {
        $errors = [];
        $success = [];

        foreach ($sessions as $session) {
            $checkEmptyColumns = $this->checkEmptyColumn($session);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'SchoolSession headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['session'];
            } else {
                $success[] = $checkEmptyColumns['session'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($sessions)
    {
        $existingColumn = ['name'];
        $headers = array_keys($sessions);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($session)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($session);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check name details
        if (isKeyExitsAndNullData('name', $session)) {
            $isEmptyColumn = true;
            $session['error']['name'] = $this->requiredMessage('name');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'session' => $session, 'missing_headers' => $checkHeaderData];
    }
}
