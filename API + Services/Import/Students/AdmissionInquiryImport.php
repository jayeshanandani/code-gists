<?php

namespace App\import\Students;

use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Students\AdmissionInquiry;
use App\Import\ValidateImportDataTrait;

class AdmissionInquiryImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($admissionInquiries)
    {
        $result = $this->validate('studentAdmissions', $admissionInquiries);
        $studentAdmissions = $this->admissionInquiries();

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $admissionInquiry) {
                if (!keyExists($admissionInquiry['email'], $studentAdmissions)) {
                    $admissionInquiry['authorized_user_id'] = auth()->user()->id;

                    $student = AdmissionInquiry::create(array_merge($admissionInquiry, $this->setSchoolIdAndUuid()));
                    $studentAdmissions[$student->email] = $student->id;

                    $this->importAddress($admissionInquiry, $student->id, AdmissionInquiry::class);
                } else {
                    array_push($result['errors'], $admissionInquiry);
                }
            }

            return response()->json(['success' => 'Import Student Admission Inquiry Data successfully'], 200);
        }
    }
}
