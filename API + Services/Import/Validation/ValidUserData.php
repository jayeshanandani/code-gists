<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Import\ImportRepeatedDataTrait;

class ValidUserData
{
    use ImportRepeatedDataTrait;

    public function userData($users)
    {
        $errors = [];
        $success = [];
        $roles = $this->roles();
        $cities = $this->cities();
        $states = $this->states();
        $countries = $this->countries();
        $usersData = $this->users();

        $validType = true;
        $types = Type::whereIn('alias', ['gender', 'blood_group'])->with('lookups')->get()->groupBy('alias')->all();
        $genderLookups = $types['gender']->first()->lookups->pluck('id', 'name')->all();
        $bloodGroupLookups = $types['blood_group']->first()->lookups->pluck('id', 'name')->all();

        foreach ($users as $user) {
            $checkEmptyColumns = $this->checkEmptyColumn($user, $roles, $cities, $states, $countries, $usersData, $genderLookups, $bloodGroupLookups);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'user headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['user'];
            } else {
                $success[] = $checkEmptyColumns['user'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($user)
    {
        $existingColumn = ['role', 'gender', 'bloodGroup', 'email', 'password', 'first_name', 'address_1', 'city', 'state', 'country'];
        $headers = array_keys($user);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($user, $roles, $cities, $states, $countries, $usersData, $genderLookups, $bloodGroupLookups)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($user);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check role_id  details
        if (isKeyExitsAndNotNullData('role', $user) && !keyExists($user['role'], $roles)) {
            $isEmptyColumn = true;
            $user['error']['role'] = 'role details not found.';
        } elseif (isKeyExitsAndNotNullData('role', $user) && keyExists($user['role'], $roles)) {
            $user['role_id'] = $roles[$user['role']];
        }

        // check gender_id  details
        if (isKeyExitsAndNotNullData('gender', $user) && !keyExists($user['gender'], $genderLookups)) {
            $isEmptyColumn = true;
            $user['error']['gender'] = 'gender details not found.';
        } elseif (isKeyExitsAndNotNullData('gender', $user) && keyExists($user['gender'], $genderLookups)) {
            $user['gender_id'] = $genderLookups[$user['gender']];
        }

        // check blood_group_id  details
        if (isKeyExitsAndNotNullData('bloodGroup', $user) && !keyExists($user['bloodGroup'], $bloodGroupLookups)) {
            $isEmptyColumn = true;
            $user['error']['bloodGroup'] = 'bloodGroup details not found.';
        } elseif (isKeyExitsAndNotNullData('bloodGroup', $user) && keyExists($user['bloodGroup'], $bloodGroupLookups)) {
            $user['blood_group_id'] = $bloodGroupLookups[$user['bloodGroup']];
        }

        // check city_id  details
        if (isKeyExitsAndNotNullData('city', $user) && !keyExists($user['city'], $cities)) {
            $isEmptyColumn = true;
            $user['error']['city'] = 'city details not found.';
        } elseif (isKeyExitsAndNotNullData('city', $user) && keyExists($user['city'], $cities)) {
            $user['city_id'] = $cities[$user['city']];
        }

        // check state_id  details
        if (isKeyExitsAndNotNullData('state', $user) && !keyExists($user['state'], $states)) {
            $isEmptyColumn = true;
            $user['error']['state'] = 'state details not found.';
        } elseif (isKeyExitsAndNotNullData('state', $user) && keyExists($user['state'], $states)) {
            $user['state_id'] = $states[$user['state']];
        }

        // check country_id  details
        if (isKeyExitsAndNotNullData('country', $user) && !keyExists($user['country'], $countries)) {
            $isEmptyColumn = true;
            $user['error']['country'] = 'country details not found.';
        } elseif (isKeyExitsAndNotNullData('country', $user) && keyExists($user['country'], $countries)) {
            $user['country_id'] = $countries[$user['country']];
        }

        //check phone number valid
        if (isKeyExitsAndNotNullData('phone_no', $user) && isset($user['phone_no'])) {
            if (isValidPhoneNo($user['phone_no'])) {
                $isEmptyColumn = true;
                $user['error']['phone_no'] = 'invalid phone_number found.';
            }
        }

        //check email exits
        if (isKeyExitsAndNotNullData('email', $user) && !isValidEmail($user['email']) && keyExists($user['email'], $usersData)) {
            $isEmptyColumn = true;
            $user['error']['email'] = 'email already exits.';
        }

        //check email valid
        if (isKeyExitsAndNotNullData('email', $user)) {
            if (isValidEmail($user['email'])) {
                $isEmptyColumn = true;
                $user['error']['email'] = 'invalid email found.';
            }
        }

        //check password valid
        if (isKeyExitsAndNotNullData('password', $user)) {
            if (!isValidPassword($user['password'])) {
                $isEmptyColumn = true;
                $user['error']['password'] = 'invalid password found.';
            }
        }

        //check role details
        if (isKeyExitsAndNullData('role', $user)) {
            $isEmptyColumn = true;
            $user['error']['role'] = $this->requiredMessage('role');
        }
        //check gender details
        if (isKeyExitsAndNullData('gender', $user)) {
            $isEmptyColumn = true;
            $user['error']['gender'] = $this->requiredMessage('gender');
        }
        //check bloodGroup details
        if (isKeyExitsAndNullData('bloodGroup', $user)) {
            $isEmptyColumn = true;
            $user['error']['bloodGroup'] = $this->requiredMessage('bloodGroup');
        }
        //check email details
        if (isKeyExitsAndNullData('email', $user)) {
            $isEmptyColumn = true;
            $user['error']['email'] = $this->requiredMessage('email');
        }
        //check password details
        if (isKeyExitsAndNullData('password', $user)) {
            $isEmptyColumn = true;
            $user['error']['password'] = $this->requiredMessage('password');
        }
        //check first_name details
        if (isKeyExitsAndNullData('first_name', $user)) {
            $isEmptyColumn = true;
            $user['error']['first_name'] = $this->requiredMessage('first_name');
        }
        //check address_1 details
        if (isKeyExitsAndNullData('address_1', $user)) {
            $isEmptyColumn = true;
            $user['error']['address_1'] = $this->requiredMessage('address_1');
        }
        //check city details
        if (isKeyExitsAndNullData('city', $user)) {
            $isEmptyColumn = true;
            $user['error']['city'] = $this->requiredMessage('city');
        }
        //check state details
        if (isKeyExitsAndNullData('state', $user)) {
            $isEmptyColumn = true;
            $user['error']['state'] = $this->requiredMessage('state');
        }
        //check country details
        if (isKeyExitsAndNullData('country', $user)) {
            $isEmptyColumn = true;
            $user['error']['country'] = $this->requiredMessage('country');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'user' => $user, 'missing_headers' => $checkHeaderData];
    }
}
