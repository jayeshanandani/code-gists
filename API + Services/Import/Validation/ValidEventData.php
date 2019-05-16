<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidEventData
{
    use ImportRepeatedDataTrait;

    public function eventData($events)
    {
        $errors = [];
        $success = [];

        foreach ($events as $event) {
            $checkEmptyColumns = $this->checkEmptyColumn($event);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Event headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['event'];
            } else {
                $success[] = $checkEmptyColumns['event'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($events)
    {
        $existingColumn = ['title', 'content', 'event_access', 'address_1', 'city', 'state', 'country'];
        $headers = array_keys($events);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($event)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($event);
        $isEmptyColumn = (!empty($checkHeaderData));

        //check title details
        if (isKeyExitsAndNullData('title', $event)) {
            $isEmptyColumn = true;
            $event['error']['title'] = $this->requiredMessage('title');
        }

        //check content details
        if (isKeyExitsAndNullData('content', $event)) {
            $isEmptyColumn = true;
            $event['error']['content'] = $this->requiredMessage('content');
        }

        //check event_access details
        if (isKeyExitsAndNullData('event_access', $event)) {
            $isEmptyColumn = true;
            $event['error']['event_access'] = $this->requiredMessage('event_access');
        }

        //check address_1 details
        if (isKeyExitsAndNullData('address_1', $event)) {
            $isEmptyColumn = true;
            $event['error']['address_1'] = $this->requiredMessage('address_1');
        }

        //check city details
        if (isKeyExitsAndNullData('city', $event)) {
            $isEmptyColumn = true;
            $event['error']['city'] = $this->requiredMessage('city');
        }

        //check state details
        if (isKeyExitsAndNullData('state', $event)) {
            $isEmptyColumn = true;
            $event['error']['state'] = $this->requiredMessage('state');
        }

        //check country details
        if (isKeyExitsAndNullData('country', $event)) {
            $isEmptyColumn = true;
            $event['error']['country'] = $this->requiredMessage('country');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'event' => $event, 'missing_headers' => $checkHeaderData];
    }
}
