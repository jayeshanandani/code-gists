<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidLectureData
{
    use ImportRepeatedDataTrait;

    public function LectureData($lectures)
    {
        $errors = [];
        $success = [];
        $departments = $this->schoolDepartments();
        $classRoom = $this->classRooms();
        $classRoomUser = $this->classRoomUsers();

        foreach ($lectures as $lecture) {
            $isEmptyColumn = true;

            $checkEmptyColumns = $this->checkEmptyColumn($lecture, $classRoom, $classRoomUser);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'lecture headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['lecture'];
            } else {
                $success[] = $checkEmptyColumns['lecture'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($lectures)
    {
        $existingColumn = ['class', 'lecture_date', 'start_time', 'end_time'];
        $headers = array_keys($lectures);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($lecture, $classRoom, $classRoomUser)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($lecture);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check class_id  details
        if (isKeyExitsAndNotNullData('class', $lecture) && !keyExists($lecture['class'], $classRoom)) {
            $isEmptyColumn = true;
            $lecture['error']['class'] = 'class details not found.';
        } elseif (isKeyExitsAndNotNullData('class', $lecture) && keyExists($lecture['class'], $classRoom)) {
            $lecture['class_room_id'] = $classRoom[$lecture['class']];
            $lecture['user_id'] = $classRoomUser[$lecture['class']];
        }

        //check class details
        if (isKeyExitsAndNullData('class', $lecture)) {
            $isEmptyColumn = true;
            $lecture['error']['class'] = $this->requiredMessage('class');
        }
        //check lecture_date details
        if (isKeyExitsAndNullData('lecture_date', $lecture)) {
            $isEmptyColumn = true;
            $lecture['error']['lecture_date'] = $this->requiredMessage('lecture_date');
        }
        //check start_time details
        if (isKeyExitsAndNullData('start_time', $lecture)) {
            $isEmptyColumn = true;
            $lecture['error']['start_time'] = $this->requiredMessage('start_time');
        }
        //check end_time details
        if (isKeyExitsAndNullData('end_time', $lecture)) {
            $isEmptyColumn = true;
            $lecture['error']['end_time'] = $this->requiredMessage('end_time');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'lecture' => $lecture, 'missing_headers' => $checkHeaderData];
    }
}
