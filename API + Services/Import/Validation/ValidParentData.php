<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Students\StudentParent;
use App\Import\ImportRepeatedDataTrait;

class ValidParentData
{
    use ImportRepeatedDataTrait;

    public function ParentData($parents)
    {
        $errors = [];
        $success = [];
        $relations = $this->studentRelations();
        $students = $this->students();
        $cities = $this->cities();
        $states = $this->states();
        $countries = $this->countries();
        $types = Type::whereIn('alias', ['gender'])->with('lookups')->get()->groupBy('alias')->all();
        $genderLookups = $types['gender']->first()->lookups->pluck('id', 'name')->all();
        $studentsParents = StudentParent::select('id', 'full_name', 'email', 'relation_id', 'student_id')->get();

        $parentsArray = [];

        foreach ($studentsParents as $studentsParent) {
            $parentsArray[$studentsParent['student_id'] . $studentsParent['relation_id'] . $studentsParent['email']] = $studentsParent['id'];
        }

        foreach ($parents as $parent) {
            $checkEmptyColumns = $this->checkEmptyColumn($parent, $students, $relations, $cities, $states, $countries, $genderLookups, $parentsArray);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'parent headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['parent'];
            } else {
                $success[] = $checkEmptyColumns['parent'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($parents)
    {
        $existingColumn = ['student', 'relation', 'gender', 'full_name', 'email', 'address_1', 'city', 'state', 'country'];
        $headers = array_keys($parents);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($parent, $students, $relations, $cities, $states, $countries, $genderLookups, $parentsArray)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($parent);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check student_id  details
        if (isKeyExitsAndNotNullData('student', $parent) && !keyExists($parent['student'], $students)) {
            $isEmptyColumn = true;
            $parent['error']['student'] = 'student details not found.';
        } elseif (isKeyExitsAndNotNullData('student', $parent) && keyExists($parent['student'], $students)) {
            $parent['student_id'] = $students[$parent['student']];
        }

        // check relation_id  details
        if (isKeyExitsAndNotNullData('relation', $parent) && !keyExists($parent['relation'], $relations)) {
            $isEmptyColumn = true;
            $parent['error']['relation'] = 'relation details not found.';
        } elseif (isKeyExitsAndNotNullData('relation', $parent) && keyExists($parent['relation'], $relations)) {
            $parent['relation_id'] = $relations[$parent['relation']];
        }

        // check gender_id  details
        if (isKeyExitsAndNotNullData('gender', $parent) && !keyExists($parent['gender'], $genderLookups)) {
            $isEmptyColumn = true;
            $parent['error']['gender'] = 'gender details not found.';
        } elseif (isKeyExitsAndNotNullData('gender', $parent) && keyExists($parent['gender'], $genderLookups)) {
            $parent['gender_id'] = $genderLookups[$parent['gender']];
        }

        // check city_id  details
        if (isKeyExitsAndNotNullData('city', $parent) && !keyExists($parent['city'], $cities)) {
            $isEmptyColumn = true;
            $parent['error']['city'] = 'city details not found.';
        } elseif (isKeyExitsAndNotNullData('city', $parent) && keyExists($parent['city'], $cities)) {
            $parent['city_id'] = $cities[$parent['city']];
        }

        // check state_id  details
        if (isKeyExitsAndNotNullData('state', $parent) && !keyExists($parent['state'], $states)) {
            $isEmptyColumn = true;
            $parent['error']['state'] = 'state details not found.';
        } elseif (isKeyExitsAndNotNullData('state', $parent) && keyExists($parent['state'], $states)) {
            $parent['state_id'] = $states[$parent['state']];
        }

        // check country_id  details
        if (isKeyExitsAndNotNullData('country', $parent) && !keyExists($parent['country'], $countries)) {
            $isEmptyColumn = true;
            $parent['error']['country'] = 'country details not found.';
        } elseif (isKeyExitsAndNotNullData('country', $parent) && keyExists($parent['country'], $countries)) {
            $parent['country_id'] = $countries[$parent['country']];
        }

        //check email exits
        if (isKeyExitsAndNotNullData('email', $parent) && !isValidEmail($parent['email']) &&
            isKeyExitsAndNotNullData('student', $parent) && isKeyExitsAndNotNullData('relation', $parent)
            && keyExists($parent['student'], $students) && keyExists($parent['relation'], $relations)) {
            $key = $students[$parent['student']] . $relations[$parent['relation']] . $parent['email'];

            //check parent unique email
            if (keyExists($key, $parentsArray)) {
                $isEmptyColumn = true;
                $parent['error']['email'] = 'Email already exits.';
            }
        }

        //check email valid
        if (isKeyExitsAndNotNullData('email', $parent)) {
            if (isValidEmail($parent['email'])) {
                $isEmptyColumn = true;
                $parent['error']['email'] = 'invalid email found.';
            }
        }

        //check phone number valid
        if (isKeyExitsAndNotNullData('phone_no', $parent) && isset($parent['phone_no'])) {
            if (isValidPhoneNo($parent['phone_no'])) {
                $isEmptyColumn = true;
                $parent['error']['phone_no'] = 'invalid phone_number found.';
            }
        }

        //check mobile number valid
        if (isKeyExitsAndNotNullData('mobile_no', $parent) && isset($parent['mobile_no'])) {
            if (isValidPhoneNo($parent['mobile_no'])) {
                $isEmptyColumn = true;
                $parent['error']['mobile_no'] = 'invalid mobile_number found.';
            }
        }

        //check student details
        if (isKeyExitsAndNullData('student', $parent)) {
            $isEmptyColumn = true;
            $parent['error']['student'] = $this->requiredMessage('student');
        }
        //check relation details
        if (isKeyExitsAndNullData('relation', $parent)) {
            $isEmptyColumn = true;
            $parent['error']['relation'] = $this->requiredMessage('relation');
        }
        //check gender details
        if (isKeyExitsAndNullData('gender', $parent)) {
            $isEmptyColumn = true;
            $parent['error']['gender'] = $this->requiredMessage('gender');
        }
        //check full_name details
        if (isKeyExitsAndNullData('full_name', $parent)) {
            $isEmptyColumn = true;
            $parent['error']['full_name'] = $this->requiredMessage('full_name');
        }
        //check email details
        if (isKeyExitsAndNullData('email', $parent)) {
            $isEmptyColumn = true;
            $parent['error']['email'] = $this->requiredMessage('email');
        }
        //check address_1 details
        if (isKeyExitsAndNullData('address_1', $parent)) {
            $isEmptyColumn = true;
            $parent['error']['address_1'] = $this->requiredMessage('address_1');
        }
        //check city details
        if (isKeyExitsAndNullData('city', $parent)) {
            $isEmptyColumn = true;
            $parent['error']['city'] = $this->requiredMessage('city');
        }
        //check state details
        if (isKeyExitsAndNullData('state', $parent)) {
            $isEmptyColumn = true;
            $parent['error']['state'] = $this->requiredMessage('state');
        }
        //check country details
        if (isKeyExitsAndNullData('country', $parent)) {
            $isEmptyColumn = true;
            $parent['error']['country'] = $this->requiredMessage('country');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'parent' => $parent, 'missing_headers' => $checkHeaderData];
    }
}
