<?php

namespace App\import\StandardGrades;

use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\StandardGrades\StandardGrade;
use App\Import\ValidateImportDataTrait;
use App\StandardGrades\StandardGradeMap;

class StandardGradesImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($grades)
    {
        $result = $this->validate('standardGrade', $grades);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $grade) {
                if (isSuperAdmin()) {
                    $grade['external_id'] = config('thunderSchool.external.super_admin.external_id');
                    $grade['external_type'] = config('thunderSchool.external.super_admin.external_type');
                } elseif (isDistrictAdmin()) {
                    $grade['external_id'] = auth()->user()->district_id;
                    $grade['external_type'] = config('thunderSchool.external.district_admin.external_type');
                } elseif (isSchoolAdmin()) {
                    $grade['external_id'] = auth()->user()->school_id;
                    $grade['external_type'] = config('thunderSchool.external.school_admin.external_type');
                } else {
                    return response()->json(['error' => 'You are not authorized.'], 400);
                }
                $grade['uuid'] = generateUuid();
                $standardGrade = StandardGrade::create($grade);

                if (isSchoolAdmin()) {
                    $data = [
                        'standard_grade_id' => $standardGrade->id,
                    ];

                    StandardGradeMap::create(array_merge($data, $this->setSchoolIdAndUuid()));
                }
            }

            return response()->json(['success' => 'Import Standard Grades Data successfully'], 200);
        }
    }
}
