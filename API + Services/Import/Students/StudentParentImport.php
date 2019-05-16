<?php

namespace App\import\Students;

use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Students\StudentParent;
use App\Import\ValidateImportDataTrait;

class StudentParentImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($data)
    {
        $result = $this->validate('parent', $data);

        $studentsParents = StudentParent::select('id', 'full_name', 'email', 'relation_id', 'student_id')->get();

        $parentsArray = [];

        foreach ($studentsParents as $studentsParent) {
            $parentsArray[$studentsParent['student_id'] . $studentsParent['relation_id'] . $studentsParent['email']] = $studentsParent['id'];
        }

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $data) {
                $key = $data['student_id'] . $data['relation_id'] . $data['email'];

                if (!keyExists($key, $parentsArray)) {
                    $data['uuid'] = generateUuid();
                    $studentParent = StudentParent::create($data);

                    $parentsArray[$studentParent->student_id . $studentParent->relation_id . $studentParent->email] = $studentParent->id;

                    $address = $this->importAddress($data, $studentParent->id, StudentParent::class);
                } else {
                    $data['error']['email'] = 'Email already exits.';
                    array_push($result['errors'], $data);
                }
            }

            return response()->json(['success' => 'Import Student Parent Data successfully'], 200);
        }
    }
}
