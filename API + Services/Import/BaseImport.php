<?php

namespace App\Import;

use App\Users\User;
use App\Addresses\Address;
use Illuminate\Support\Str;

class BaseImport
{
    use ImportRepeatedDataTrait;

    public function importUser($data, $districtId = null, $schoolId = null)
    {
        $data['uuid'] = generateUuid();
        $data['district_id'] = $districtId;
        $data['school_id'] = $schoolId;
        $user = User::create($data);

        return $user;
    }

    public function importAddress($data, $externalId, $externalType)
    {
        $data['uuid'] = generateUuid();
        $data['external_id'] = $externalId;
        $data['external_type'] = $externalType;
        $address = Address::create($data);

        return $address;
    }

    public function setSchoolIdAndUuid()
    {
        return   [
            'uuid'      => (string) Str::orderedUuid(),
            'school_id' => (request('school_id') ? request('school_id') : auth()->user()->school_id),
      ];
    }
}
