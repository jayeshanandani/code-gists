<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidHomeworkData
{
    use ImportRepeatedDataTrait;

    public function HomeworkData($homeworks)
    {
        $errors = [];
        $success = [];
        $classRoom = $this->classRooms();
        $classRoomUser = $this->classRoomUsers();

        foreach ($homeworks as $homework) {
            $checkEmptyColumns = $this->checkEmptyColumn($homework, $classRoom, $classRoomUser);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'homework headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['homework'];
            } else {
                $success[] = $checkEmptyColumns['homework'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($homeworks)
    {
        $existingColumn = ['class', 'title', 'due_date', 'start_time', 'end_time'];
        $headers = array_keys($homeworks);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($homework, $classRoom, $classRoomUser)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($homework);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check class_id  details
        if (isKeyExitsAndNotNullData('class', $homework) && !keyExists($homework['class'], $classRoom)) {
            $isEmptyColumn = true;
            $homework['error']['class'] = 'class details not found.';
        } elseif (isKeyExitsAndNotNullData('class', $homework) && keyExists($homework['class'], $classRoom)) {
            $homework['class_room_id'] = $classRoom[$homework['class']];
            $homework['user_id'] = $classRoomUser[$homework['class']];
        }

        //check class details
        if (isKeyExitsAndNullData('class', $homework)) {
            $isEmptyColumn = true;
            $homework['error']['class'] = $this->requiredMessage('class');
        }
        //check title details
        if (isKeyExitsAndNullData('title', $homework)) {
            $isEmptyColumn = true;
            $homework['error']['title'] = $this->requiredMessage('title');
        }
        //check due_date details
        if (isKeyExitsAndNullData('due_date', $homework)) {
            $isEmptyColumn = true;
            $homework['error']['due_date'] = $this->requiredMessage('due_date');
        }
        //check start_time details
        if (isKeyExitsAndNullData('start_time', $homework)) {
            $isEmptyColumn = true;
            $homework['error']['start_time'] = $this->requiredMessage('start_time');
        }
        //check end_time details
        if (isKeyExitsAndNullData('end_time', $homework)) {
            $isEmptyColumn = true;
            $homework['error']['end_time'] = $this->requiredMessage('end_time');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'homework' => $homework, 'missing_headers' => $checkHeaderData];
    }
}
