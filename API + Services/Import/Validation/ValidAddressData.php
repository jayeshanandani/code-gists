<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidAddressData
{
    use ImportRepeatedDataTrait;

    public function AddressData($addresses)
    {
        $errors = [];
        $success = [];
        $cities = $this->cities();
        $states = $this->states();
        $countries = $this->countries();

        foreach ($addresses as $address) {
            if (!keyExists($address['city'], $cities) ||
                !keyExists($address['state'], $states) ||
                !keyExists($address['country'], $countries)) {
                $errors[] = $address;
            } else {
                $address['city_id'] = $cities[$address['city']];
                $address['state_id'] = $states[$address['state']];
                $address['country_id'] = $countries[$address['country']];
                $success[] = $address;
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }
}
