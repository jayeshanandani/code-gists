<?php

namespace App\Exports\Questions;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class QuestionsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('questions')
        ->leftjoin('exams', 'exams.id', '=', 'questions.exam_id')
        ->leftjoin('schools', 'schools.id', '=', 'exams.school_id')
        ->leftjoin('lookups as questionType', 'questionType.id', '=', 'questions.question_type_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
            'questions.question_text as QUESTION TEXT',
            'questionType.name as QUESTION TYPES',
            'exams.name as EXAM NAME',
            'questions.marks as MARKS',
            'questions.is_published as IS PUBLISHED'
        );
    }
}
