<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidEventRegistrationData
{
    use ImportRepeatedDataTrait;

    public function eventRegisterData($eventRegistrations)
    {
        $errors = [];
        $success = [];
        $events = $this->events();

        foreach ($eventRegistrations as $eventRegistration) {
            $checkEmptyColumns = $this->checkEmptyColumn($eventRegistration, $events);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'eventRegistration headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['eventRegistration'];
            } else {
                $success[] = $checkEmptyColumns['eventRegistration'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($eventRegistrations)
    {
        $existingColumn = ['name', 'email', 'event'];
        $headers = array_keys($eventRegistrations);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($eventRegistration, $events)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($eventRegistration);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check event_id  details
        if (isKeyExitsAndNotNullData('event', $eventRegistration) && !keyExists($eventRegistration['event'], $events)) {
            $isEmptyColumn = true;
            $eventRegistration['error']['event'] = 'Event details not found.';
        } elseif (isKeyExitsAndNotNullData('event', $eventRegistration) && keyExists($eventRegistration['event'], $events)) {
            $eventRegistration['event_id'] = $events[$eventRegistration['event']];
        }

        //check name details
        if (isKeyExitsAndNullData('name', $eventRegistration)) {
            $isEmptyColumn = true;
            $eventRegistration['error']['name'] = $this->requiredMessage('name');
        }
        //check event details
        if (isKeyExitsAndNullData('event', $eventRegistration)) {
            $isEmptyColumn = true;
            $eventRegistration['error']['event'] = $this->requiredMessage('event');
        }
        //check email details
        if (isKeyExitsAndNullData('email', $eventRegistration)) {
            $isEmptyColumn = true;
            $eventRegistration['error']['email'] = $this->requiredMessage('email');
        }
        //check email valid
        if (isKeyExitsAndNotNullData('email', $eventRegistration)) {
            if (isValidEmail($eventRegistration['email'])) {
                $isEmptyColumn = true;
                $eventRegistration['error']['email'] = 'invalid email found.';
            }
        }

        //check mobile number valid
        if (isKeyExitsAndNotNullData('mobile_no', $eventRegistration) && isset($eventRegistration['mobile_no'])) {
            if (isValidPhoneNo($eventRegistration['mobile_no'])) {
                $isEmptyColumn = true;
                $eventRegistration['error']['mobile_number'] = 'Invalid mobile_number found.';
            }
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'eventRegistration' => $eventRegistration, 'missing_headers' => $checkHeaderData];
    }
}
