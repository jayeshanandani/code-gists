<?php

namespace App\import\Validation;

use App\Books\BookCategory;
use App\Import\ImportRepeatedDataTrait;

class validBookStoreData
{
    use ImportRepeatedDataTrait;

    public function bookStoreData($bookStores)
    {
        $errors = [];
        $success = [];
        $currentUser = auth()->user();

        if ($currentUser->role_id == config('thunderSchool.roles.district_admin')) {
            $categories = BookCategory::where('district_id', $currentUser->district_id)->whereNull('school_id')->pluck('id', 'name')->all();
        } elseif ($currentUser->role_id === config('thunderSchool.roles.school_admin')) {
            $categories = BookCategory::where('school_id', $currentUser->school_id)->pluck('id', 'name')->all();
        }

        foreach ($bookStores as $bookStore) {
            $checkEmptyColumns = $this->checkEmptyColumn($bookStore, $categories);

            if ($checkEmptyColumns['isEmptyColumn']) {
                if ($checkEmptyColumns['missing_headers']) {
                    $errors['headers']['headings'] = $checkEmptyColumns['missing_headers'];
                    $errors['headers']['message'] = 'bookStore headers are missing.';
                }
                $errors['data'][] = $checkEmptyColumns['bookStore'];
            } else {
                $success[] = $checkEmptyColumns['bookStore'];
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }

    public function checkHeaders($bookStores)
    {
        $existingColumn = ['category', 'publisher_name', 'title', 'isbn_number', 'author_name', 'description'];
        $headers = array_keys($bookStores);

        return array_values(array_diff($existingColumn, $headers));
    }

    public function checkEmptyColumn($bookStore, $categories)
    {
        $isEmptyColumn = false;

        // check Headers
        $checkHeaderData = $this->checkHeaders($bookStore);
        $isEmptyColumn = (!empty($checkHeaderData));

        // check category_id  details
        if (isKeyExitsAndNotNullData('category', $bookStore) && !keyExists($bookStore['category'], $categories)) {
            $isEmptyColumn = true;
            $bookStore['error']['category'] = 'category details not found.';
        } elseif (isKeyExitsAndNotNullData('category', $bookStore) && keyExists($bookStore['category'], $categories)) {
            $bookStore['category_id'] = $categories[$bookStore['category']];
        }

        //check category details
        if (isKeyExitsAndNullData('category', $bookStore)) {
            $isEmptyColumn = true;
            $bookStore['error']['category'] = $this->requiredMessage('category');
        }
        //check publisher_name details
        if (isKeyExitsAndNullData('publisher_name', $bookStore)) {
            $isEmptyColumn = true;
            $bookStore['error']['publisher_name'] = $this->requiredMessage('publisher_name');
        }
        //check title details
        if (isKeyExitsAndNullData('title', $bookStore)) {
            $isEmptyColumn = true;
            $bookStore['error']['title'] = $this->requiredMessage('title');
        }
        //check isbn_number details
        if (isKeyExitsAndNullData('isbn_number', $bookStore)) {
            $isEmptyColumn = true;
            $bookStore['error']['isbn_number'] = $this->requiredMessage('isbn_number');
        }
        //check author_name details
        if (isKeyExitsAndNullData('author_name', $bookStore)) {
            $isEmptyColumn = true;
            $bookStore['error']['author_name'] = $this->requiredMessage('author_name');
        }
        //check description details
        if (isKeyExitsAndNullData('description', $bookStore)) {
            $isEmptyColumn = true;
            $bookStore['error']['description'] = $this->requiredMessage('description');
        }

        return ['isEmptyColumn' => $isEmptyColumn, 'bookStore' => $bookStore, 'missing_headers' => $checkHeaderData];
    }
}
