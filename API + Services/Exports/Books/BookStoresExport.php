<?php

namespace App\Exports\Books;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class BookStoresExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('book_stores')
                        ->leftJoin('schools', 'schools.id', '=', 'book_stores.school_id')
                        ->leftJoin('districts', 'districts.id', '=', 'book_stores.district_id')
                        ->leftJoin('book_categories', 'book_categories.id', '=', 'book_stores.category_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL NAME',
            'districts.name  as DISTRICT NAME',
            'book_categories.name as BOOK CATEGORIES NAME',
            'book_stores.publisher_name as PUBLISHER NAME',
            'book_stores.title as TITLE',
            'book_stores.isbn_number as ISBN NUMBER',
            'book_stores.author_name as AUTHOR NAME',
            'book_stores.description as DESCRIPTION'
        );
    }
}
