<?php

namespace App\import\Books;

use App\Books\BookStore;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class BookStoreImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($data)
    {
        $result = $this->validate('bookStore', $data);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $data) {
                $data['district_id'] = auth()->user()->district_id;
                BookStore::create(array_merge($data, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Import Book Store Data successfully'], 200);
        }
    }
}
