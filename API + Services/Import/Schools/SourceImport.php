<?php

namespace App\import\Schools;

use App\Import\BaseImport;
use App\Schools\SchoolSource;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class SourceImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($sources)
    {
        $result = $this->validate('source', $sources);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $source) {
                SchoolSource::create(array_merge($source, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import SchoolSource Data successfully.'], 200);
        }
    }
}
