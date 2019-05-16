<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Import\ImportRepeatedDataTrait;

class ValidAppointmentData
{
    use ImportRepeatedDataTrait;

    public function AppointmentData($appointments)
    {
        $errors = [];
        $success = [];
        $studentsParents = $this->studentParents();
        $students = $this->students();
        $types = Type::whereIn('alias', ['appointment_type', 'appointment_with'])->with('lookups')->get()->groupBy('alias')->all();
        $appointmentWithLookups = $types['appointment_with']->first()->lookups->pluck('id', 'name')->all();
        $appointmentTypeLookups = $types['appointment_type']->first()->lookups->pluck('id', 'name')->all();

        foreach ($appointments as $appointment) {
            $checkEmptyColumns = $this->checkEmptyColumn($appointment, $appointmentWithLookups, $appointmentTypeLookups, $studentsParents, $students);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Appointment headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['appointment'];
            } else {
                $success[] = $checkEmptyColumns['appointment'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($appointments)
    {
        $existingColumn = ['type', 'appointment_date', 'appointment_with', 'email'];
        $headers = array_keys($appointments);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($appointment, $appointmentWithLookups, $appointmentTypeLookups, $studentsParents, $students)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($appointment);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check appointment_with  details exits
        if (isKeyExitsAndNotNullData('appointment_with', $appointment) && !keyExists($appointment['appointment_with'], $appointmentWithLookups)) {
            $isEmptyColumn = true;
            $appointment['error']['appointment_with'] = 'appointment_with details not found.';
        } elseif (isKeyExitsAndNotNullData('appointment_with', $appointment) && keyExists($appointment['appointment_with'], $appointmentWithLookups)) {
            $appointment['appointment_with_id'] = $appointmentWithLookups[$appointment['appointment_with']];
        }

        // check Email exits
        if (isKeyExitsAndNotNullData('appointment_with', $appointment) && keyExists($appointment['appointment_with'], $appointmentWithLookups) && $appointment['appointment_with'] == 'parent') {
            if (isKeyExitsAndNotNullData('email', $appointment) && !isValidEmail($appointment['email']) && !array_key_exists($appointment['email'], $studentsParents)) {
                $isEmptyColumn = true;
                $appointment['error']['parent'] = 'parent email details not found.';
            }
        } elseif (isKeyExitsAndNotNullData('appointment_with', $appointment) && keyExists($appointment['appointment_with'], $appointmentWithLookups) && $appointment['appointment_with'] == 'student') {
            if (isKeyExitsAndNotNullData('email', $appointment) && !isValidEmail($appointment['email']) && !array_key_exists($appointment['email'], $students)) {
                $isEmptyColumn = true;
                $appointment['error']['student'] = 'student email details not found.';
            }
        }

        // check Type details exits
        if (isKeyExitsAndNotNullData('type', $appointment) && !keyExists($appointment['type'], $appointmentTypeLookups)) {
            $isEmptyColumn = true;
            $appointment['error']['type'] = 'appointment type details not found.';
        } elseif (isKeyExitsAndNotNullData('type', $appointment) && keyExists($appointment['type'], $appointmentTypeLookups)) {
            $appointment['type_id'] = $appointmentTypeLookups[$appointment['type']];
        }

        //check type details
        if (isKeyExitsAndNullData('type', $appointment)) {
            $isEmptyColumn = true;
            $appointment['error']['type'] = 'appointment type is required.';
        }
        //check appointment_date details
        if (isKeyExitsAndNullData('appointment_date', $appointment)) {
            $isEmptyColumn = true;
            $appointment['error']['appointment_date'] = 'appointment_date is required.';
        }
        //check appointment_with details
        if (isKeyExitsAndNullData('appointment_with', $appointment)) {
            $isEmptyColumn = true;
            $appointment['error']['appointment_with'] = 'appointment_with is required.';
        }
        //check email details
        if (isKeyExitsAndNullData('email', $appointment)) {
            $isEmptyColumn = true;
            $appointment['error']['email'] = 'email is required.';
        }

        //check email valid
        if (isKeyExitsAndNotNullData('email', $appointment)) {
            if (isValidEmail($appointment['email'])) {
                $isEmptyColumn = true;
                $appointment['error']['email'] = 'invalid email found.';
            }
        }

        //check phone_no details
        if (isKeyExitsAndNullData('phone_no', $appointment)) {
            $isEmptyColumn = true;
            $appointment['error']['phone_no'] = $this->requiredMessage('phone_number');
        }

        //check phone number valid
        if (isKeyExitsAndNotNullData('phone_no', $appointment) && isset($appointment['phone_no'])) {
            if (isValidPhoneNo($appointment['phone_no'])) {
                $isEmptyColumn = true;
                $appointment['error']['phone_no'] = 'invalid phone_number found.';
            }
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'appointment' => $appointment, 'missing_headers' => $checkHeaderData];
    }
}
