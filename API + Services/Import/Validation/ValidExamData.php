<?php

namespace App\import\Validation;

use App\Import\ImportRepeatedDataTrait;

class ValidExamData
{
    use ImportRepeatedDataTrait;

    public function examData($exams)
    {
        $errors = [];
        $success = [];

        $grades = $this->standardGrades();
        $subjects = $this->schoolSubjects();
        $sessions = $this->schoolSessions();
        $classRoom = $this->classRooms();

        foreach ($exams as $exam) {
            if (!keyExists($exam['session'], $sessions) ||
                !keyExists($exam['grade'], $grades) ||
                !keyExists($exam['subject'], $subjects) ||
                !keyExists($exam['class'], $classRoom)) {
                $errors[] = $exam;
            } else {
                $exam['session_id'] = $sessions[$exam['session']];
                $exam['grade_id'] = $grades[$exam['grade']];
                $exam['subject_id'] = $subjects[$exam['subject']];
                $exam['class_id'] = $classRoom[$exam['class']];
                $success[] = $exam;
            }
        }

        return [
            'success' => $success,
            'errors'  => $errors,
        ];
    }
}
