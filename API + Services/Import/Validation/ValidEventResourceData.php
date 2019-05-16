<?php

namespace App\import\Validation;

use App\Masters\Type;
use App\Import\ImportRepeatedDataTrait;

class ValidEventResourceData
{
    use ImportRepeatedDataTrait;

    public function eventResourceData($eventResources)
    {
        $errors = [];
        $success = [];
        $events = $this->events();
        $types = Type::whereIn('alias', ['event_resource_status'])->with('lookups')->get()->groupBy('alias')->all();
        $resourceStatusLookups = $types['event_resource_status']->first()->lookups->pluck('id', 'name')->all();

        foreach ($eventResources as $eventResource) {
            $checkEmptyColumns = $this->checkEmptyColumn($eventResource, $events, $resourceStatusLookups);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'Event resources headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['eventResource'];
            } else {
                $success[] = $checkEmptyColumns['eventResource'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($eventResources)
    {
        $existingColumn = ['event', 'resource_name', 'quantity', 'unit_cost', 'total_cost', 'phone_no', 'event_resource_status'];
        $headers = array_keys($eventResources);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($eventResource, $events, $resourceStatusLookups)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($eventResource);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check event_id  details
        if (isKeyExitsAndNotNullData('event', $eventResource) && !keyExists($eventResource['event'], $events)) {
            $isEmptyColumn = true;
            $eventResource['error']['event'] = 'Event details not found.';
        } elseif (isKeyExitsAndNotNullData('event', $eventResource) && keyExists($eventResource['event'], $events)) {
            $eventResource['event_id'] = $events[$eventResource['event']];
        }

        // check event_resource_status_id  details
        if (isKeyExitsAndNotNullData('event_resource_status', $eventResource) && !keyExists($eventResource['event_resource_status'], $resourceStatusLookups)) {
            $isEmptyColumn = true;
            $eventResource['error']['event_resource_status'] = 'Event resource status details not found.';
        } elseif (isKeyExitsAndNotNullData('event_resource_status', $eventResource) && keyExists($eventResource['event_resource_status'], $resourceStatusLookups)) {
            $eventResource['event_resource_status_id'] = $resourceStatusLookups[$eventResource['event_resource_status']];
        }

        //check event details
        if (isKeyExitsAndNullData('event', $eventResource)) {
            $isEmptyColumn = true;
            $eventResource['error']['event'] = $this->requiredMessage('event');
        }
        //check resource_name details
        if (isKeyExitsAndNullData('resource_name', $eventResource)) {
            $isEmptyColumn = true;
            $eventResource['error']['resource_name'] = $this->requiredMessage('resource_name');
        }
        //check quantity details
        if (isKeyExitsAndNullData('quantity', $eventResource)) {
            $isEmptyColumn = true;
            $eventResource['error']['quantity'] = $this->requiredMessage('quantity');
        }
        //check unit_cost details
        if (isKeyExitsAndNullData('unit_cost', $eventResource)) {
            $isEmptyColumn = true;
            $eventResource['error']['unit_cost'] = $this->requiredMessage('unit_cost');
        }
        //check total_cost details
        if (isKeyExitsAndNullData('total_cost', $eventResource)) {
            $isEmptyColumn = true;
            $eventResource['error']['total_cost'] = $this->requiredMessage('total_cost');
        }
        //check phone_no details
        if (isKeyExitsAndNullData('phone_no', $eventResource)) {
            $isEmptyColumn = true;
            $eventResource['error']['phone_no'] = $this->requiredMessage('phone_number');
        }
        //check event_resource_status details
        if (isKeyExitsAndNullData('event_resource_status', $eventResource)) {
            $isEmptyColumn = true;
            $eventResource['error']['event_resource_status'] = $this->requiredMessage('event_resource_status');
        }

        //check phone number valid
        if (isKeyExitsAndNotNullData('phone_no', $eventResource) && isset($eventResource['phone_no'])) {
            if (isValidPhoneNo($eventResource['phone_no'])) {
                $isEmptyColumn = true;
                $eventResource['error']['phone_no'] = 'Invalid phone_number found.';
            }
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'eventResource' => $eventResource, 'missing_headers' => $checkHeaderData];
    }
}
