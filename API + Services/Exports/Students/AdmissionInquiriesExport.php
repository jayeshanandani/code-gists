<?php

namespace App\Exports\Students;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class AdmissionInquiriesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('admission_inquiries')
                            ->leftjoin('schools', 'schools.id', '=', 'admission_inquiries.school_id')
                            ->leftjoin('school_sessions', 'school_sessions.id', '=', 'admission_inquiries.session_id')
                            ->leftjoin('standard_grades', 'standard_grades.id', '=', 'admission_inquiries.standard_grade_id')
                            ->leftjoin('school_sources', 'school_sources.id', '=', 'admission_inquiries.source_id')
                            ->leftjoin('lookups as gender', 'gender.id', '=', 'admission_inquiries.gender_id')
                            ->leftjoin('users', 'users.id', '=', 'admission_inquiries.authorized_user_id')
                            ->leftjoin('lookups as inquiryStatus', 'inquiryStatus.id', '=', 'admission_inquiries.inquiry_status_id');
    }

    public function headings($query, $columns)
    {
        return !empty($columns) ? addSelectedColumn($query, $columns) : $query->addSelect(
                'schools.name as SCHOOL',
                'school_sessions.name as SCHOOL SESSION',
                'standard_grades.name as STANDARD GRADE',
                'school_sources.title as SOURCE',
                'admission_inquiries.first_name as FIRST NAME',
                'admission_inquiries.last_name as LAST NAME',
                'admission_inquiries.middle_name as MIDDLE NAME',
                'admission_inquiries.email as EMAIL',
                'admission_inquiries.phone_no as PHONE NO',
                'gender.name as gender_name as GENDER',
                'admission_inquiries.birthdate as BIRTHDATE',
                'admission_inquiries.source_details as SOUCE DETAILS',
                'admission_inquiries.social_info as SOCIAL INFO',
                'admission_inquiries.description as DESCRIPTION',
                'admission_inquiries.inquiry_date as INQUIRY DATE',
                'users.first_name as authorizedUser AUTHORIZED USER',
                'inquiryStatus.name as INQUIRY STATUS',
                'admission_inquiries.is_admission as IS ADMISSION'
        );
    }
}
