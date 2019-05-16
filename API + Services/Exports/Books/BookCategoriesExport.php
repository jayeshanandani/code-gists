<?php

namespace App\Exports\Books;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class BookCategoriesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('book_categories')
                        ->leftJoin('schools', 'schools.id', '=', 'book_categories.school_id')
                        ->leftJoin('districts', 'districts.id', '=', 'book_categories.district_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'schools.name as SCHOOL NAME',
            'districts.name as DISTRICT NAME',
            'book_categories.name as BOOK CATEGORIES NAME'
        );
    }
}
