<?php

namespace App\import\Books;

use App\Import\BaseImport;
use App\Books\BookCategory;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class BookCategoryImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($bookCategories)
    {
        $result = $this->validate('bookCategory', $bookCategories);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $bookCategory) {
                $bookCategory['district_id'] = auth()->user()->district_id;
                BookCategory::create(array_merge($bookCategory, $this->setSchoolIdAndUuid()));
            }

            return response()->json(['success' => 'Book Category Data successfully Import.'], 200);
        }
    }
}
