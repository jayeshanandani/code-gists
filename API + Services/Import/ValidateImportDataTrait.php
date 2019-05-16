<?php

namespace App\Import;

use Illuminate\Support\Facades\App;
use App\import\Validation\ValidCityData;
use App\import\Validation\ValidExamData;
use App\import\Validation\ValidTodoData;
use App\import\Validation\ValidTypeData;
use App\import\Validation\ValidUserData;
use App\import\Validation\ValidClassData;
use App\import\Validation\ValidStaffData;
use App\import\Validation\ValidStateData;
use App\import\Validation\ValidLookUpData;
use App\import\Validation\ValidParentData;
use App\import\Validation\ValidSchoolData;
use App\import\Validation\ValidSourceData;
use App\import\Validation\ValidAddressData;
use App\import\Validation\validCountryData;
use App\import\Validation\ValidLectureData;
use App\import\Validation\ValidSessionData;
use App\import\Validation\ValidStudentData;
use App\import\Validation\ValidSubjectData;
use App\import\Validation\ValidDistrictData;
use App\import\Validation\ValidHomeworkData;
use App\import\Validation\ValidLocationData;
use App\import\Validation\ValidMaterialData;
use App\import\Validation\ValidRelationData;
use App\import\Validation\validBookStoreData;
use App\import\Validation\ValidAssignmentData;
use App\import\Validation\ValidDepartmentData;
use App\import\Validation\ValidAppointmentData;
use App\import\Validation\ValidDesignationData;
use App\import\Validation\validBookCategoryData;
use App\import\Validation\ValidEventResourceData;
use App\import\Validation\validStandardGradeData;
use App\import\Validation\ValidAdmissionInquiryData;
use App\import\Validation\ValidEventRegistrationData;

trait ValidateImportDataTrait
{
    public function validate($module, $data)
    {
        switch ($module) {

            case 'user':
                $validateData = App::make(ValidUserData::class)->userData($data);

                break;

            case 'school':
                $validateData = App::make(ValidSchoolData::class)->schoolData($data);

                break;

            case 'district':
                $validateData = App::make(ValidDistrictData::class)->districtData($data);

                break;

            case 'staff':
                $validateData = App::make(ValidStaffData::class)->StaffData($data);

                break;

            case 'department':
                $validateData = App::make(ValidDepartmentData::class)->departmentData($data);

                break;

            case 'designation':
                $validateData = App::make(ValidDesignationData::class)->designationData($data);

                break;

            case 'classRoom':
                $validateData = App::make(ValidClassData::class)->ClassData($data);

                break;

            case 'lecture':
                $validateData = App::make(ValidLectureData::class)->LectureData($data);

                break;

            case 'location':
                $validateData = App::make(ValidLocationData::class)->locationData($data);

                break;

            case 'material':
                $validateData = App::make(ValidMaterialData::class)->MaterialData($data);

                break;

            case 'session':
                $validateData = App::make(ValidSessionData::class)->sessionData($data);

                break;

            case 'source':
                $validateData = App::make(ValidSourceData::class)->sourceData($data);

                break;

            case 'subject':
                $validateData = App::make(ValidSubjectData::class)->SubjectData($data);

                break;

            case 'bookCategory':
                $validateData = App::make(validBookCategoryData::class)->bookCategoryData($data);

                break;

            case 'bookStore':
                $validateData = App::make(validBookStoreData::class)->bookStoreData($data);

                break;

            case 'eventRegistration':
                $validateData = App::make(ValidEventRegistrationData::class)->eventRegisterData($data);

                break;

            case 'eventResource':
                $validateData = App::make(ValidEventResourceData::class)->eventResourceData($data);

                break;

            case 'exam':
                $validateData = App::make(ValidExamData::class)->examData($data);

                break;

            case 'student':
                $validateData = App::make(ValidStudentData::class)->StudentData($data);

                break;

            case 'studentAdmissions':
                $validateData = App::make(ValidAdmissionInquiryData::class)->AdmissionInquiryData($data);

                break;

            case 'parent':
                $validateData = App::make(ValidParentData::class)->ParentData($data);

                break;

            case 'relation':
                $validateData = App::make(ValidRelationData::class)->relationData($data);

                break;

            case 'appointment':
                $validateData = App::make(ValidAppointmentData::class)->AppointmentData($data);

                break;

            case 'address':
                $validateData = App::make(ValidAddressData::class)->AddressData($data);

                break;

            case 'homeworks':
                $validateData = App::make(ValidHomeworkData::class)->HomeworkData($data);

                break;

            case 'assignments':
                $validateData = App::make(ValidAssignmentData::class)->AssignmentData($data);

                break;

            case 'standardGrade':
                $validateData = App::make(validStandardGradeData::class)->standardGradeData($data);

                break;

            case 'type':
                $validateData = App::make(ValidTypeData::class)->typeData($data);

                break;

            case 'lookups':
                $validateData = App::make(ValidLookUpData::class)->lookupData($data);

                break;

            case 'country':
                $validateData = App::make(validCountryData::class)->countryData($data);

                break;

            case 'state':
                $validateData = App::make(ValidStateData::class)->stateData($data);

                break;

            case 'city':
                $validateData = App::make(ValidCityData::class)->cityData($data);

                break;

            case 'todo':
                $validateData = App::make(ValidTodoData::class)->todoData($data);

                break;

            default:

                break;
        }

        return $validateData;
    }
}
