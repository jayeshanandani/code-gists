<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidClassData
{
    use ImportRepeatedDataTrait;

    public function ClassData($classes)
    {
        $errors = [];
        $success = [];
        $grades = $this->standardGrades();
        $subjects = $this->schoolSubjects();
        $locations = $this->schoolLocations();
        $sessions = $this->schoolSessions();

        foreach ($classes as $class) {
            $checkEmptyColumns = $this->checkEmptyColumn($class, $grades, $subjects, $locations, $sessions);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Class-room headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['class'];
            } else {
                $checkEmptyColumns['class']['user_id'] = (request('user_id') ? request('user_id') : auth()->user()->id);
                $success[] = $checkEmptyColumns['class'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($classes)
    {
        $existingColumn = ['name', 'standardGrade', 'session', 'subject', 'location', 'max_student', 'class_days', 'start_time', 'end_time'];
        $headers = array_keys($classes);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($class, $grades, $subjects, $locations, $sessions)
    {
        $isEmptyColumn = false;

        //check Headers
        $checkHeaderData = $this->checkHeaders($class);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check standard_grade_id details
        if (isKeyExitsAndNotNullData('standardGrade', $class) && !keyExists($class['standardGrade'], $grades)) {
            $isEmptyColumn = true;
            $class['error']['standardGrade'] = 'StandardGrade details not found.';
        } elseif (isKeyExitsAndNotNullData('standardGrade', $class) && keyExists($class['standardGrade'], $grades)) {
            $class['standard_grade_id'] = $grades[$class['standardGrade']];
        }

        // check subject_id details
        if (isKeyExitsAndNotNullData('subject', $class) && !keyExists($class['subject'], $subjects)) {
            $isEmptyColumn = true;
            $class['error']['subject'] = 'Subject details not found.';
        } elseif (isKeyExitsAndNotNullData('subject', $class) && keyExists($class['subject'], $subjects)) {
            $class['subject_id'] = $subjects[$class['subject']];
        }

        // check location_id details
        if (isKeyExitsAndNotNullData('location', $class) && !keyExists($class['location'], $locations)) {
            $isEmptyColumn = true;
            $class['error']['location'] = 'Location details not found.';
        } elseif (isKeyExitsAndNotNullData('location', $class) && keyExists($class['location'], $locations)) {
            $class['location_id'] = $locations[$class['location']];
        }

        //check session_id details
        if (isKeyExitsAndNotNullData('session', $class) && !keyExists($class['session'], $sessions)) {
            $isEmptyColumn = true;
            $class['error']['session'] = 'Session details not found.';
        } elseif (isKeyExitsAndNotNullData('session', $class) && keyExists($class['session'], $sessions)) {
            $class['session_id'] = $sessions[$class['session']];
        }

        //check name details
        if (isKeyExitsAndNullData('name', $class)) {
            $isEmptyColumn = true;
            $class['error']['name'] = $this->requiredMessage('name');
        }
        //check standardGrade details
        if (isKeyExitsAndNullData('standardGrade', $class)) {
            $isEmptyColumn = true;
            $class['error']['standardGrade'] = $this->requiredMessage('standardGrade');
        }
        //check session details
        if (isKeyExitsAndNullData('session', $class)) {
            $isEmptyColumn = true;
            $class['error']['session'] = $this->requiredMessage('session');
        }
        //check subject details
        if (isKeyExitsAndNullData('subject', $class)) {
            $isEmptyColumn = true;
            $class['error']['subject'] = $this->requiredMessage('subject');
        }
        //check location details
        if (isKeyExitsAndNullData('location', $class)) {
            $isEmptyColumn = true;
            $class['error']['location'] = $this->requiredMessage('location');
        }
        //check max_student details
        if (isKeyExitsAndNullData('max_student', $class)) {
            $isEmptyColumn = true;
            $class['error']['max_student'] = $this->requiredMessage('max_student');
        }
        //check class_days details
        if (isKeyExitsAndNullData('class_days', $class)) {
            $isEmptyColumn = true;
            $class['error']['class_days'] = $this->requiredMessage('class_days');
        }
        //check start_time details
        if (isKeyExitsAndNullData('start_time', $class)) {
            $isEmptyColumn = true;
            $class['error']['start_time'] = $this->requiredMessage('start_time');
        }
        //check end_time details
        if (isKeyExitsAndNullData('end_time', $class)) {
            $isEmptyColumn = true;
            $class['error']['end_time'] = $this->requiredMessage('end_time');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'class' => $class, 'missing_headers' => $checkHeaderData];
    }
}
