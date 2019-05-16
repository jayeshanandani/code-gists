<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Import\ImportRepeatedDataTrait;

class ValidSchoolData
{
    use ImportRepeatedDataTrait;

    public function SchoolData($schools)
    {
        $errors = [];
        $success = [];
        $roles = $this->roles();
        $cities = $this->cities();
        $states = $this->states();
        $countries = $this->countries();
        $users = $this->users();

        $validType = true;
        $types = Type::whereIn('alias', ['gender', 'blood_group'])->with('lookups')->get()->groupBy('alias')->all();
        $genderLookups = $types['gender']->first()->lookups->pluck('id', 'name')->all();
        $bloodGroupLookups = $types['blood_group']->first()->lookups->pluck('id', 'name')->all();

        foreach ($schools as $school) {
            $checkEmptyColumns = $this->checkEmptyColumn($school, $roles, $cities, $states, $countries, $users, $genderLookups, $bloodGroupLookups);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'school headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['school'];
            } else {
                $success[] = $checkEmptyColumns['school'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($school)
    {
        $existingColumn = ['name', 'max_student', 'role', 'gender', 'bloodGroup', 'email', 'password', 'first_name', 'address_1', 'city', 'state', 'country'];
        $headers = array_keys($school);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($school, $roles, $cities, $states, $countries, $users, $genderLookups, $bloodGroupLookups)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($school);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check role_id  details
        if (isKeyExitsAndNotNullData('role', $school) && !keyExists($school['role'], $roles)) {
            $isEmptyColumn = true;
            $school['error']['role'] = 'role details not found.';
        } elseif (isKeyExitsAndNotNullData('role', $school) && keyExists($school['role'], $roles)) {
            $school['role_id'] = $roles[$school['role']];
        }

        // check gender_id  details
        if (isKeyExitsAndNotNullData('gender', $school) && !keyExists($school['gender'], $genderLookups)) {
            $isEmptyColumn = true;
            $school['error']['gender'] = 'gender details not found.';
        } elseif (isKeyExitsAndNotNullData('gender', $school) && keyExists($school['gender'], $genderLookups)) {
            $school['gender_id'] = $genderLookups[$school['gender']];
        }

        // check blood_group_id  details
        if (isKeyExitsAndNotNullData('bloodGroup', $school) && !keyExists($school['bloodGroup'], $bloodGroupLookups)) {
            $isEmptyColumn = true;
            $school['error']['bloodGroup'] = 'bloodGroup details not found.';
        } elseif (isKeyExitsAndNotNullData('bloodGroup', $school) && keyExists($school['bloodGroup'], $bloodGroupLookups)) {
            $school['blood_group_id'] = $bloodGroupLookups[$school['bloodGroup']];
        }

        // check city_id  details
        if (isKeyExitsAndNotNullData('city', $school) && !keyExists($school['city'], $cities)) {
            $isEmptyColumn = true;
            $school['error']['city'] = 'city details not found.';
        } elseif (isKeyExitsAndNotNullData('city', $school) && keyExists($school['city'], $cities)) {
            $school['city_id'] = $cities[$school['city']];
        }

        // check state_id  details
        if (isKeyExitsAndNotNullData('state', $school) && !keyExists($school['state'], $states)) {
            $isEmptyColumn = true;
            $school['error']['state'] = 'state details not found.';
        } elseif (isKeyExitsAndNotNullData('state', $school) && keyExists($school['state'], $states)) {
            $school['state_id'] = $states[$school['state']];
        }

        // check country_id  details
        if (isKeyExitsAndNotNullData('country', $school) && !keyExists($school['country'], $countries)) {
            $isEmptyColumn = true;
            $school['error']['country'] = 'country details not found.';
        } elseif (isKeyExitsAndNotNullData('country', $school) && keyExists($school['country'], $countries)) {
            $school['country_id'] = $countries[$school['country']];
        }

        //check email exits
        if (isKeyExitsAndNotNullData('email', $school) && !isValidEmail($school['email']) && keyExists($school['email'], $users)) {
            $isEmptyColumn = true;
            $school['error']['email'] = 'email already exits.';
        }

        //check email valid
        if (isKeyExitsAndNotNullData('email', $school)) {
            if (isValidEmail($school['email'])) {
                $isEmptyColumn = true;
                $school['error']['email'] = 'invalid email found.';
            }
        }

        //check password valid
        if (isKeyExitsAndNotNullData('password', $school)) {
            if (!isValidPassword($school['password'])) {
                $isEmptyColumn = true;
                $school['error']['password'] = 'invalid password found.';
            }
        }

        //check phone number valid
        if (isKeyExitsAndNotNullData('phone_no', $school) && isset($school['phone_no'])) {
            if (isValidPhoneNo($school['phone_no'])) {
                $isEmptyColumn = true;
                $school['error']['phone_no'] = 'invalid phone_number found.';
            }
        }

        //check name details
        if (isKeyExitsAndNullData('name', $school)) {
            $isEmptyColumn = true;
            $school['error']['name'] = $this->requiredMessage('name');
        }
        //check max_student details
        if (isKeyExitsAndNullData('max_student', $school)) {
            $isEmptyColumn = true;
            $school['error']['max_student'] = $this->requiredMessage('max_student');
        }
        //check role details
        if (isKeyExitsAndNullData('role', $school)) {
            $isEmptyColumn = true;
            $school['error']['role'] = $this->requiredMessage('role');
        }
        //check gender details
        if (isKeyExitsAndNullData('gender', $school)) {
            $isEmptyColumn = true;
            $school['error']['gender'] = $this->requiredMessage('gender');
        }
        //check bloodGroup details
        if (isKeyExitsAndNullData('bloodGroup', $school)) {
            $isEmptyColumn = true;
            $school['error']['bloodGroup'] = $this->requiredMessage('bloodGroup');
        }
        //check email details
        if (isKeyExitsAndNullData('email', $school)) {
            $isEmptyColumn = true;
            $school['error']['email'] = $this->requiredMessage('email');
        }
        //check password details
        if (isKeyExitsAndNullData('password', $school)) {
            $isEmptyColumn = true;
            $school['error']['password'] = $this->requiredMessage('password');
        }
        //check first_name details
        if (isKeyExitsAndNullData('first_name', $school)) {
            $isEmptyColumn = true;
            $school['error']['first_name'] = $this->requiredMessage('first_name');
        }
        //check address_1 details
        if (isKeyExitsAndNullData('address_1', $school)) {
            $isEmptyColumn = true;
            $school['error']['address_1'] = $this->requiredMessage('address_1');
        }
        //check city details
        if (isKeyExitsAndNullData('city', $school)) {
            $isEmptyColumn = true;
            $school['error']['city'] = $this->requiredMessage('city');
        }
        //check state details
        if (isKeyExitsAndNullData('state', $school)) {
            $isEmptyColumn = true;
            $school['error']['state'] = $this->requiredMessage('state');
        }
        //check country details
        if (isKeyExitsAndNullData('country', $school)) {
            $isEmptyColumn = true;
            $school['error']['country'] = $this->requiredMessage('country');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'school' => $school, 'missing_headers' => $checkHeaderData];
    }
}
