<?php

namespace App\Import;

use App\import\Exams\ExamImport;
use App\import\Todos\TodoImport;
use App\import\Users\UserImport;
use App\import\Events\EventImport;
use App\import\Master\TypesImport;
use App\import\Master\CitiesImport;
use App\import\Master\StatesImport;
use App\import\Schools\ClassImport;
use App\import\Schools\GradeImport;
use App\import\Schools\StaffImport;
use App\import\Master\LookupsImport;
use App\import\Schools\SchoolImport;
use App\import\Schools\SourceImport;
use App\import\Books\BookStoreImport;
use App\import\Master\DistrictImport;
use App\import\Schools\LectureImport;
use App\import\Schools\SessionImport;
use App\import\Schools\SubjectImport;
use App\import\Master\CountriesImport;
use App\import\Schools\LocationImport;
use App\import\Schools\MaterialImport;
use App\import\Students\StudentImport;
use App\import\Books\BookCategoryImport;
use App\import\Schools\DepartmentImport;
use App\import\Schools\GradeScaleImport;
use App\import\Homeworks\HomeworksImport;
use App\import\Schools\DesignationImport;
use App\import\Schools\SchoolAdminImport;
use App\import\Events\EventResourceImport;
use App\import\Students\StudentParentImport;
use App\import\Assignments\AssignmentsImport;
use App\import\Events\EventRegistrationImport;
use App\import\Students\StudentRelationImport;
use App\import\Appointments\AppointmentsImport;
use App\import\Students\AdmissionInquiryImport;
use App\import\StandardGrades\StandardGradesImport;

class ImportStrategy
{
    protected $importModule;

    public function __construct($module)
    {
        $module = request('module');

        switch ($module) {

            case 'districts':
                $this->importModule = new DistrictImport();

                break;

            case 'schools':
                $this->importModule = new SchoolImport();

                break;

            case 'staffs':
                $this->importModule = new StaffImport();

                break;

            case 'schoolAdmin':
                $this->importModule = new SchoolAdminImport();

                break;

            case 'schoolSession':
                $this->importModule = new SessionImport();

                break;

            case 'schoolSource':
                $this->importModule = new SourceImport();

                break;

            case 'departments':
                $this->importModule = new DepartmentImport();

                break;

            case 'designations':
                $this->importModule = new DesignationImport();

                break;

            case 'subjects':
                $this->importModule = new SubjectImport();

                break;

            case 'materials':
                $this->importModule = new MaterialImport();

                break;

            case 'locations':
                $this->importModule = new LocationImport();

                break;

            case 'classRooms':
                $this->importModule = new ClassImport();

                break;

            case 'lectures':
                $this->importModule = new LectureImport();

                break;

            case 'grades':
                $this->importModule = new GradeImport();

                break;

            case 'gradeScales':
                $this->importModule = new GradeScaleImport();

                break;

            case 'students':
                $this->importModule = new StudentImport();

                break;

            case 'admissionInquiries':
                $this->importModule = new AdmissionInquiryImport();

                break;

            case 'studentRelation':
                $this->importModule = new StudentRelationImport();

                break;

            case 'studentParent':
                $this->importModule = new StudentParentImport();

                break;

            case 'bookCategories':
                $this->importModule = new BookCategoryImport();

                break;

            case 'bookStores':
                $this->importModule = new BookStoreImport();

                break;

            case 'events':
                $this->importModule = new EventImport();

                break;

            case 'eventRegistrations':
                $this->importModule = new EventRegistrationImport();

                break;

            case 'eventResources':
                $this->importModule = new EventResourceImport();

                break;

            case 'appointment':
                $this->importModule = new AppointmentsImport();

                break;

            case 'todo':
                $this->importModule = new TodoImport();

                break;

            case 'exams':
                $this->importModule = new ExamImport();

                break;

            case 'users':
                $this->importModule = new UserImport();

                break;

            case 'countries':
                $this->importModule = new CountriesImport();

                break;

            case 'states':
                $this->importModule = new StatesImport();

                break;

            case 'cities':
                $this->importModule = new CitiesImport();

                break;

            case 'types':
                $this->importModule = new TypesImport();

                break;

            case 'lookups':
                $this->importModule = new LookupsImport();

                break;

            case 'homeworks':
                $this->importModule = new HomeworksImport();

                break;

            case 'assignments':
                $this->importModule = new AssignmentsImport();

                break;

            case 'standardGrades':
                $this->importModule = new StandardGradesImport();

                break;

            default:
                return response()->json('Data was not import, try Again.');

                break;
        }
    }

    public function import($data)
    {
        return $this->importModule->import($data);
    }
}
