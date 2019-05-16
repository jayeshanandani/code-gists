<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidAssignmentData
{
    use ImportRepeatedDataTrait;

    public function AssignmentData($assignments)
    {
        $errors = [];
        $success = [];
        $classRoom = $this->classRooms();
        $classRoomUser = $this->classRoomUsers();

        foreach ($assignments as $assignment) {
            $checkEmptyColumns = $this->checkEmptyColumn($assignment, $classRoom, $classRoomUser);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Assignment headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['assignment'];
            } else {
                $success[] = $checkEmptyColumns['assignment'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($assignments)
    {
        $existingColumn = ['class', 'title', 'due_date'];
        $headers = array_keys($assignments);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($assignment, $classRoom, $classRoomUser)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($assignment);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check class_id  details
        if (isKeyExitsAndNotNullData('class', $assignment) && !keyExists($assignment['class'], $classRoom)) {
            $isEmptyColumn = true;
            $assignment['error']['class'] = 'class details not found.';
        } elseif (isKeyExitsAndNotNullData('class', $assignment) && keyExists($assignment['class'], $classRoom)) {
            $assignment['class_room_id'] = $classRoom[$assignment['class']];
            $assignment['user_id'] = $classRoomUser[$assignment['class']];
        }

        //check class details
        if (isKeyExitsAndNullData('class', $assignment)) {
            $isEmptyColumn = true;
            $assignment['error']['class'] = $this->requiredMessage('class');
        }
        //check title details
        if (isKeyExitsAndNullData('title', $assignment)) {
            $isEmptyColumn = true;
            $assignment['error']['title'] = $this->requiredMessage('title');
        }
        //check due_date details
        if (isKeyExitsAndNullData('due_date', $assignment)) {
            $isEmptyColumn = true;
            $assignment['error']['due_date'] = $this->requiredMessage('due_date');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'assignment' => $assignment, 'missing_headers' => $checkHeaderData];
    }
}
