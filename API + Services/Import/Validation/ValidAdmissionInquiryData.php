<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Import\ImportRepeatedDataTrait;

class ValidAdmissionInquiryData
{
    use ImportRepeatedDataTrait;

    public function AdmissionInquiryData($admissionInquiries)
    {
        $errors = [];
        $success = [];
        $grades = $this->standardGrades();
        $sessions = $this->schoolSessions();
        $sources = $this->schoolSources();
        $cities = $this->cities();
        $states = $this->states();
        $countries = $this->countries();
        $studentAdmissions = $this->admissionInquiries();
        $types = Type::whereIn('alias', ['gender', 'inquiry_status'])->with('lookups')->get()->groupBy('alias')->all();
        $genderLookups = $types['gender']->first()->lookups->pluck('id', 'name')->all();
        $inquiryStatusLookups = $types['inquiry_status']->first()->lookups->pluck('id', 'name')->all();

        foreach ($admissionInquiries as $admissionInquiry) {
            $checkEmptyColumns = $this->checkEmptyColumn($admissionInquiry, $cities, $states, $countries, $studentAdmissions, $genderLookups, $sessions, $grades, $sources, $inquiryStatusLookups);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'admission inquiry headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['admissionInquiry'];
            } else {
                $success[] = $checkEmptyColumns['admissionInquiry'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($admissionInquiries)
    {
        $existingColumn = ['first_name', 'last_name', 'gender', 'standardGrade', 'session', 'source', 'email', 'phone_no', 'source_details', 'inquiry_date', 'address_1', 'city', 'state', 'country'];
        $headers = array_keys($admissionInquiries);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($admissionInquiry, $cities, $states, $countries, $studentAdmissions, $genderLookups, $sessions, $grades, $sources, $inquiryStatusLookups)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($admissionInquiry);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check gender_id  details
        if (isKeyExitsAndNotNullData('gender', $admissionInquiry) && !keyExists($admissionInquiry['gender'], $genderLookups)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['gender'] = 'gender details not found.';
        } elseif (isKeyExitsAndNotNullData('gender', $admissionInquiry) && keyExists($admissionInquiry['gender'], $genderLookups)) {
            $admissionInquiry['gender_id'] = $genderLookups[$admissionInquiry['gender']];
        }

        // check session_id  details
        if (isKeyExitsAndNotNullData('session', $admissionInquiry) && !keyExists($admissionInquiry['session'], $sessions)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['session'] = 'session details not found.';
        } elseif (isKeyExitsAndNotNullData('session', $admissionInquiry) && keyExists($admissionInquiry['session'], $sessions)) {
            $admissionInquiry['session_id'] = $sessions[$admissionInquiry['session']];
        }

        // check inquiry_status_id  details
        if (isKeyExitsAndNotNullData('inquiry_status', $admissionInquiry) && !keyExists($admissionInquiry['inquiry_status'], $inquiryStatusLookups)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['inquiry_status'] = 'inquiry_status details not found.';
        } elseif (isKeyExitsAndNotNullData('inquiry_status', $admissionInquiry) && keyExists($admissionInquiry['inquiry_status'], $inquiryStatusLookups)) {
            $admissionInquiry['inquiry_status_id'] = $inquiryStatusLookups[$admissionInquiry['inquiry_status']];
        }

        // check source_id  details
        if (isKeyExitsAndNotNullData('source', $admissionInquiry) && !keyExists($admissionInquiry['source'], $sources)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['source'] = 'source details not found.';
        } elseif (isKeyExitsAndNotNullData('source', $admissionInquiry) && keyExists($admissionInquiry['source'], $sources)) {
            $admissionInquiry['source_id'] = $sources[$admissionInquiry['source']];
        }

        // check standard_grade_id  details
        if (isKeyExitsAndNotNullData('standardGrade', $admissionInquiry) && !keyExists($admissionInquiry['standardGrade'], $grades)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['standardGrade'] = 'standardGrade details not found.';
        } elseif (isKeyExitsAndNotNullData('standardGrade', $admissionInquiry) && keyExists($admissionInquiry['standardGrade'], $grades)) {
            $admissionInquiry['standard_grade_id'] = $grades[$admissionInquiry['standardGrade']];
        }

        // check city_id  details
        if (isKeyExitsAndNotNullData('city', $admissionInquiry) && !keyExists($admissionInquiry['city'], $cities)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['city'] = 'city details not found.';
        } elseif (isKeyExitsAndNotNullData('city', $admissionInquiry) && keyExists($admissionInquiry['city'], $cities)) {
            $admissionInquiry['city_id'] = $cities[$admissionInquiry['city']];
        }

        // check state_id  details
        if (isKeyExitsAndNotNullData('state', $admissionInquiry) && !keyExists($admissionInquiry['state'], $states)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['state'] = 'state details not found.';
        } elseif (isKeyExitsAndNotNullData('state', $admissionInquiry) && keyExists($admissionInquiry['state'], $states)) {
            $admissionInquiry['state_id'] = $states[$admissionInquiry['state']];
        }

        // check country_id  details
        if (isKeyExitsAndNotNullData('country', $admissionInquiry) && !keyExists($admissionInquiry['country'], $countries)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['country'] = 'country details not found.';
        } elseif (isKeyExitsAndNotNullData('country', $admissionInquiry) && keyExists($admissionInquiry['country'], $countries)) {
            $admissionInquiry['country_id'] = $countries[$admissionInquiry['country']];
        }

        //check email exits
        if (isKeyExitsAndNotNullData('email', $admissionInquiry) && !isValidEmail($admissionInquiry['email']) && keyExists($admissionInquiry['email'], $studentAdmissions)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['email'] = 'email already exits.';
        }

        //check email valid
        if (isKeyExitsAndNotNullData('email', $admissionInquiry)) {
            if (isValidEmail($admissionInquiry['email'])) {
                $isEmptyColumn = true;
                $admissionInquiry['error']['email'] = 'invalid email found.';
            }
        }

        //check phone number valid
        if (isKeyExitsAndNotNullData('phone_no', $admissionInquiry) && isset($admissionInquiry['phone_no'])) {
            if (isValidPhoneNo($admissionInquiry['phone_no'])) {
                $isEmptyColumn = true;
                $admissionInquiry['error']['phone_no'] = 'invalid phone_number found.';
            }
        }

        //check first_name details
        if (isKeyExitsAndNullData('first_name', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['first_name'] = $this->requiredMessage('first_name');
        }
        //check last_name details
        if (isKeyExitsAndNullData('last_name', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['last_name'] = $this->requiredMessage('last_name');
        }
        //check gender details
        if (isKeyExitsAndNullData('gender', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['gender'] = $this->requiredMessage('gender');
        }
        //check standardGrade details
        if (isKeyExitsAndNullData('standardGrade', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['standardGrade'] = $this->requiredMessage('standardGrade');
        }
        //check session details
        if (isKeyExitsAndNullData('session', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['session'] = $this->requiredMessage('session');
        }
        //check source details
        if (isKeyExitsAndNullData('source', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['source'] = $this->requiredMessage('source');
        }
        //check email details
        if (isKeyExitsAndNullData('email', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['email'] = $this->requiredMessage('email');
        }
        //check phone_no details
        if (isKeyExitsAndNullData('phone_no', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['phone_no'] = $this->requiredMessage('phone_no');
        }
        //check source_details details
        if (isKeyExitsAndNullData('source_details', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['source_details'] = $this->requiredMessage('source_details');
        }
        //check inquiry_date details
        if (isKeyExitsAndNullData('inquiry_date', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['inquiry_date'] = $this->requiredMessage('inquiry_date');
        }
        //check address_1 details
        if (isKeyExitsAndNullData('address_1', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['address_1'] = $this->requiredMessage('address_1');
        }
        //check city details
        if (isKeyExitsAndNullData('city', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['city'] = $this->requiredMessage('city');
        }
        //check state details
        if (isKeyExitsAndNullData('state', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['state'] = $this->requiredMessage('state');
        }
        //check country details
        if (isKeyExitsAndNullData('country', $admissionInquiry)) {
            $isEmptyColumn = true;
            $admissionInquiry['error']['country'] = $this->requiredMessage('country');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'admissionInquiry' => $admissionInquiry, 'missing_headers' => $checkHeaderData];
    }
}
