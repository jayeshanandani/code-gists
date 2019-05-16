<?php

namespace App\import\Master;

use App\Masters\District;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class DistrictImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($districts)
    {
        $result = $this->validate('district', $districts);
        $users = $this->users();
        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $district) {
                if (!keyExists($district['email'], $users)) {
                    $district['uuid'] = generateUuid();
                    $districtData = District::create($district);

                    $districtAdmin = $this->importUser($district, $districtData->id);

                    $districtData->update(['authorized_user_id' => $districtAdmin->id]);
                    $users[$districtAdmin->email] = $districtAdmin->id;

                    $this->importAddress($district, $districtData->id, District::class);
                } else {
                    array_push($result['errors'], $district);
                }
            }

            return response()->json(['success' => 'Import District Data successfully'], 200);
        }
    }
}
