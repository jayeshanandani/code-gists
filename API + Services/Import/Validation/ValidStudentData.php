<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Import\ImportRepeatedDataTrait;

class ValidStudentData
{
    use ImportRepeatedDataTrait;

    public function StudentData($students)
    {
        $errors = [];
        $success = [];
        $grades = $this->standardGrades();
        $sessions = $this->schoolSessions();
        $cities = $this->cities();
        $states = $this->states();
        $countries = $this->countries();
        $studentsData = $this->students();
        $types = Type::whereIn('alias', ['gender'])->with('lookups')->get()->groupBy('alias')->all();
        $genderLookups = $types['gender']->first()->lookups->pluck('id', 'name')->all();

        foreach ($students as $student) {
            $checkEmptyColumns = $this->checkEmptyColumn($student, $cities, $states, $countries, $studentsData, $genderLookups, $sessions, $grades);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'student headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['student'];
            } else {
                $success[] = $checkEmptyColumns['student'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($students)
    {
        $existingColumn = ['first_name', 'last_name', 'gender', 'standardGrade', 'session', 'email', 'phone_no', 'address_1', 'city', 'state', 'country'];
        $headers = array_keys($students);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($student, $cities, $states, $countries, $studentsData, $genderLookups, $sessions, $grades)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($student);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check gender_id  details
        if (isKeyExitsAndNotNullData('gender', $student) && !keyExists($student['gender'], $genderLookups)) {
            $isEmptyColumn = true;
            $student['error']['gender'] = 'gender details not found.';
        } elseif (isKeyExitsAndNotNullData('gender', $student) && keyExists($student['gender'], $genderLookups)) {
            $student['gender_id'] = $genderLookups[$student['gender']];
        }

        // check session_id  details
        if (isKeyExitsAndNotNullData('session', $student) && !keyExists($student['session'], $sessions)) {
            $isEmptyColumn = true;
            $student['error']['session'] = 'session details not found.';
        } elseif (isKeyExitsAndNotNullData('session', $student) && keyExists($student['session'], $sessions)) {
            $student['session_id'] = $sessions[$student['session']];
        }

        // check standard_grade_id  details
        if (isKeyExitsAndNotNullData('standardGrade', $student) && !keyExists($student['standardGrade'], $grades)) {
            $isEmptyColumn = true;
            $student['error']['standardGrade'] = 'standardGrade details not found.';
        } elseif (isKeyExitsAndNotNullData('standardGrade', $student) && keyExists($student['standardGrade'], $grades)) {
            $student['standard_grade_id'] = $grades[$student['standardGrade']];
        }

        // check city_id  details
        if (isKeyExitsAndNotNullData('city', $student) && !keyExists($student['city'], $cities)) {
            $isEmptyColumn = true;
            $student['error']['city'] = 'city details not found.';
        } elseif (isKeyExitsAndNotNullData('city', $student) && keyExists($student['city'], $cities)) {
            $student['city_id'] = $cities[$student['city']];
        }

        // check state_id  details
        if (isKeyExitsAndNotNullData('state', $student) && !keyExists($student['state'], $states)) {
            $isEmptyColumn = true;
            $student['error']['state'] = 'state details not found.';
        } elseif (isKeyExitsAndNotNullData('state', $student) && keyExists($student['state'], $states)) {
            $student['state_id'] = $states[$student['state']];
        }

        // check country_id  details
        if (isKeyExitsAndNotNullData('country', $student) && !keyExists($student['country'], $countries)) {
            $isEmptyColumn = true;
            $student['error']['country'] = 'country details not found.';
        } elseif (isKeyExitsAndNotNullData('country', $student) && keyExists($student['country'], $countries)) {
            $student['country_id'] = $countries[$student['country']];
        }

        //check email exits
        if (isKeyExitsAndNotNullData('email', $student) && !isValidEmail($student['email']) && keyExists($student['email'], $studentsData)) {
            $isEmptyColumn = true;
            $student['error']['email'] = 'email already exits.';
        }

        //check email valid
        if (isKeyExitsAndNotNullData('email', $student)) {
            if (isValidEmail($student['email'])) {
                $isEmptyColumn = true;
                $student['error']['email'] = 'invalid email found.';
            }
        }

        //check phone number valid
        if (isKeyExitsAndNotNullData('phone_no', $student) && isset($student['phone_no'])) {
            if (isValidPhoneNo($student['phone_no'])) {
                $isEmptyColumn = true;
                $student['error']['phone_no'] = 'invalid phone_number found.';
            }
        }

        //check mobile number valid
        if (isKeyExitsAndNotNullData('mobile_no', $student) && isset($student['mobile_no'])) {
            if (isValidPhoneNo($student['mobile_no'])) {
                $isEmptyColumn = true;
                $student['error']['mobile_no'] = 'invalid mobile_number found.';
            }
        }

        //check first_name details
        if (isKeyExitsAndNullData('first_name', $student)) {
            $isEmptyColumn = true;
            $student['error']['first_name'] = $this->requiredMessage('first_name');
        }
        //check last_name details
        if (isKeyExitsAndNullData('last_name', $student)) {
            $isEmptyColumn = true;
            $student['error']['last_name'] = $this->requiredMessage('last_name');
        }
        //check gender details
        if (isKeyExitsAndNullData('gender', $student)) {
            $isEmptyColumn = true;
            $student['error']['gender'] = $this->requiredMessage('gender');
        }
        //check standardGrade details
        if (isKeyExitsAndNullData('standardGrade', $student)) {
            $isEmptyColumn = true;
            $student['error']['standardGrade'] = $this->requiredMessage('standardGrade');
        }
        //check session details
        if (isKeyExitsAndNullData('session', $student)) {
            $isEmptyColumn = true;
            $student['error']['session'] = $this->requiredMessage('session');
        }
        //check email details
        if (isKeyExitsAndNullData('email', $student)) {
            $isEmptyColumn = true;
            $student['error']['email'] = $this->requiredMessage('email');
        }
        //check phone_no details
        if (isKeyExitsAndNullData('phone_no', $student)) {
            $isEmptyColumn = true;
            $student['error']['phone_no'] = $this->requiredMessage('phone_no');
        }
        //check address_1 details
        if (isKeyExitsAndNullData('address_1', $student)) {
            $isEmptyColumn = true;
            $student['error']['address_1'] = $this->requiredMessage('address_1');
        }
        //check city details
        if (isKeyExitsAndNullData('city', $student)) {
            $isEmptyColumn = true;
            $student['error']['city'] = $this->requiredMessage('city');
        }
        //check state details
        if (isKeyExitsAndNullData('state', $student)) {
            $isEmptyColumn = true;
            $student['error']['state'] = $this->requiredMessage('state');
        }
        //check country details
        if (isKeyExitsAndNullData('country', $student)) {
            $isEmptyColumn = true;
            $student['error']['country'] = $this->requiredMessage('country');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'student' => $student, 'missing_headers' => $checkHeaderData];
    }
}
