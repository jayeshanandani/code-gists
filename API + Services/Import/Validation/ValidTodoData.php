<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidTodoData
{
    use ImportRepeatedDataTrait;

    public function todoData($todos)
    {
        $errors = [];
        $success = [];

        foreach ($todos as $todo) {
            $checkEmptyColumns = $this->checkEmptyColumn($todo);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'todo headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['todo'];
            } else {
                $success[] = $checkEmptyColumns['todo'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($todos)
    {
        $existingColumn = ['description'];
        $headers = array_keys($todos);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($todo)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($todo);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check description details
        if (isKeyExitsAndNullData('description', $todo)) {
            $isEmptyColumn = true;
            $todo['error']['description'] = $this->requiredMessage('description');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'todo' => $todo, 'missing_headers' => $checkHeaderData];
    }
}
