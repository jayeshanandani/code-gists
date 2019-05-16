<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Import\ImportRepeatedDataTrait;

class ValidDistrictData
{
    use ImportRepeatedDataTrait;

    public function districtData($districts)
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

        foreach ($districts as $district) {
            $checkEmptyColumns = $this->checkEmptyColumn($district, $roles, $cities, $states, $countries, $users, $genderLookups, $bloodGroupLookups);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'district headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['district'];
            } else {
                $success[] = $checkEmptyColumns['district'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($district)
    {
        $existingColumn = ['name', 'role', 'gender', 'bloodGroup', 'email', 'password', 'first_name', 'address_1', 'city', 'state', 'country'];
        $headers = array_keys($district);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($district, $roles, $cities, $states, $countries, $users, $genderLookups, $bloodGroupLookups)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($district);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check role_id  details
        if (isKeyExitsAndNotNullData('role', $district) && !keyExists($district['role'], $roles)) {
            $isEmptyColumn = true;
            $district['error']['role'] = 'role details not found.';
        } elseif (isKeyExitsAndNotNullData('role', $district) && keyExists($district['role'], $roles)) {
            $district['role_id'] = $roles[$district['role']];
        }

        // check gender_id  details
        if (isKeyExitsAndNotNullData('gender', $district) && !keyExists($district['gender'], $genderLookups)) {
            $isEmptyColumn = true;
            $district['error']['gender'] = 'gender details not found.';
        } elseif (isKeyExitsAndNotNullData('gender', $district) && keyExists($district['gender'], $genderLookups)) {
            $district['gender_id'] = $genderLookups[$district['gender']];
        }

        // check blood_group_id  details
        if (isKeyExitsAndNotNullData('bloodGroup', $district) && !keyExists($district['bloodGroup'], $bloodGroupLookups)) {
            $isEmptyColumn = true;
            $district['error']['bloodGroup'] = 'bloodGroup details not found.';
        } elseif (isKeyExitsAndNotNullData('bloodGroup', $district) && keyExists($district['bloodGroup'], $bloodGroupLookups)) {
            $district['blood_group_id'] = $bloodGroupLookups[$district['bloodGroup']];
        }

        // check city_id  details
        if (isKeyExitsAndNotNullData('city', $district) && !keyExists($district['city'], $cities)) {
            $isEmptyColumn = true;
            $district['error']['city'] = 'city details not found.';
        } elseif (isKeyExitsAndNotNullData('city', $district) && keyExists($district['city'], $cities)) {
            $district['city_id'] = $cities[$district['city']];
        }

        // check state_id  details
        if (isKeyExitsAndNotNullData('state', $district) && !keyExists($district['state'], $states)) {
            $isEmptyColumn = true;
            $district['error']['state'] = 'state details not found.';
        } elseif (isKeyExitsAndNotNullData('state', $district) && keyExists($district['state'], $states)) {
            $district['state_id'] = $states[$district['state']];
        }

        // check country_id  details
        if (isKeyExitsAndNotNullData('country', $district) && !keyExists($district['country'], $countries)) {
            $isEmptyColumn = true;
            $district['error']['country'] = 'country details not found.';
        } elseif (isKeyExitsAndNotNullData('country', $district) && keyExists($district['country'], $countries)) {
            $district['country_id'] = $countries[$district['country']];
        }

        //check email exits
        if (isKeyExitsAndNotNullData('email', $district) && !isValidEmail($district['email']) && keyExists($district['email'], $users)) {
            $isEmptyColumn = true;
            $district['error']['email'] = 'email already exits.';
        }

        //check email valid
        if (isKeyExitsAndNotNullData('email', $district)) {
            if (isValidEmail($district['email'])) {
                $isEmptyColumn = true;
                $district['error']['email'] = 'invalid email found.';
            }
        }

        //check password valid
        if (isKeyExitsAndNotNullData('password', $district)) {
            if (!isValidPassword($district['password'])) {
                $isEmptyColumn = true;
                $district['error']['password'] = 'invalid password found.';
            }
        }

        //check phone number valid
        if (isKeyExitsAndNotNullData('phone_no', $district) && isset($district['phone_no'])) {
            if (isValidPhoneNo($district['phone_no'])) {
                $isEmptyColumn = true;
                $district['error']['phone_no'] = 'invalid phone_number found.';
            }
        }

        //check name details
        if (isKeyExitsAndNullData('name', $district)) {
            $isEmptyColumn = true;
            $district['error']['name'] = $this->requiredMessage('name');
        }
        //check role details
        if (isKeyExitsAndNullData('role', $district)) {
            $isEmptyColumn = true;
            $district['error']['role'] = $this->requiredMessage('role');
        }
        //check gender details
        if (isKeyExitsAndNullData('gender', $district)) {
            $isEmptyColumn = true;
            $district['error']['gender'] = $this->requiredMessage('gender');
        }
        //check bloodGroup details
        if (isKeyExitsAndNullData('bloodGroup', $district)) {
            $isEmptyColumn = true;
            $district['error']['bloodGroup'] = $this->requiredMessage('bloodGroup');
        }
        //check email details
        if (isKeyExitsAndNullData('email', $district)) {
            $isEmptyColumn = true;
            $district['error']['email'] = $this->requiredMessage('email');
        }
        //check password details
        if (isKeyExitsAndNullData('password', $district)) {
            $isEmptyColumn = true;
            $district['error']['password'] = $this->requiredMessage('password');
        }
        //check first_name details
        if (isKeyExitsAndNullData('first_name', $district)) {
            $isEmptyColumn = true;
            $district['error']['first_name'] = $this->requiredMessage('first_name');
        }
        //check address_1 details
        if (isKeyExitsAndNullData('address_1', $district)) {
            $isEmptyColumn = true;
            $district['error']['address_1'] = $this->requiredMessage('address_1');
        }
        //check city details
        if (isKeyExitsAndNullData('city', $district)) {
            $isEmptyColumn = true;
            $district['error']['city'] = $this->requiredMessage('city');
        }
        //check state details
        if (isKeyExitsAndNullData('state', $district)) {
            $isEmptyColumn = true;
            $district['error']['state'] = $this->requiredMessage('state');
        }
        //check country details
        if (isKeyExitsAndNullData('country', $district)) {
            $isEmptyColumn = true;
            $district['error']['country'] = $this->requiredMessage('country');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'district' => $district, 'missing_headers' => $checkHeaderData];
    }
}
