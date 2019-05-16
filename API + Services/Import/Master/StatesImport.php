<?php

namespace App\import\Master;

use App\Masters\State;
use Illuminate\Support\Str;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class StatesImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($statesData)
    {
        $result = $this->validate('state', $statesData);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $state) {
                $state['uuid'] = (string) Str::orderedUuid();
                State::create($state);
            }

            return response()->json(['success' => 'Import States Data successfully'], 200);
        }
    }
}
