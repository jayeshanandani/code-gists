<?php

namespace App\import\Students;

use App\Students\Student;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class StudentImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($students)
    {
        $result = $this->validate('student', $students);
        $studentsData = $this->students();

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $student) {
                if (!keyExists($student['email'], $studentsData)) {
                    $student['admission_inquiry_id'] = (!empty($student['admission_inquiry_id'])) ? $student['admission_inquiry_id'] : null;

                    $studentData = Student::create(array_merge($student, $this->setSchoolIdAndUuid()));
                    $studentsData[$studentData->email] = $studentData->id;

                    $this->importAddress($student, $studentData->id, Student::class);
                } else {
                    array_push($result['errors'], $student);
                }
            }

            return response()->json(['success' => 'Import Student Data successfully.'], 200);
        }
    }
}
