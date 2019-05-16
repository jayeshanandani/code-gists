<?php

namespace App\Exports\Queries;

use Carbon\Carbon;
use App\Schools\School;

class QueryProvider
{
    protected $query;

    protected $module;

    protected $export;

    public function queryMaster($module)
    {
        $this->module = $module;
        switch ($this->module) {

            case config('thunderSchool.module.appointments'):
                $this->export = "App\Exports\Appointment\AppointmentsExport";

                break;
            case config('thunderSchool.module.books.bookCategories'):
                $this->export = "App\Exports\Books\BookCategoriesExport";

                break;
            case config('thunderSchool.module.books.bookStores'):
                $this->export = "App\Exports\Books\BookStoresExport";

                break;
            case config('thunderSchool.module.events.eventRegistrations'):
                $this->export = "App\Exports\Events\EventRegistrationsExport";

                break;
            case config('thunderSchool.module.events.eventResources'):
                $this->export = "App\Exports\Events\EventResourcesExport";

                break;
            case config('thunderSchool.module.events.events'):
                $this->export = "App\Exports\Events\EventsExport";

                break;
            // case config('thunderSchool.module.exams'):
            //     $this->query = $this->returnExamsJoinQuery();

            //     break;
            case config('thunderSchool.module.homeworks'):
                $this->export = "App\Exports\Homework\HomeworksExport";

                break;
            case config('thunderSchool.module.masters.cities'):
                $this->export = "App\Exports\Masters\CitiesExport";

                break;
            case config('thunderSchool.module.masters.districts'):
                $this->export = "App\Exports\Masters\DistrictsExport";

                break;
            case config('thunderSchool.module.masters.lookups'):
                $this->export = "App\Exports\Masters\LookupsExport";

                break;
            case config('thunderSchool.module.masters.subjectTypes'):
                $this->export = "App\Exports\Masters\SubjectTypesExport";

                break;
            case config('thunderSchool.module.masters.types'):
                $this->export = "App\Exports\Masters\TypesExport";

                break;
            case config('thunderSchool.module.masters.states'):
                $this->export = "App\Exports\Masters\StatesExport";

                break;
            case config('thunderSchool.module.masters.countries'):
                $this->export = "App\Exports\Masters\CountriesExport";

                break;
            case config('thunderSchool.module.questions'):
                $this->export = "App\Exports\Questions\QuestionsExport";

                break;
            case config('thunderSchool.module.schools.classes'):
                $this->export = "App\Exports\Schools\ClassRoomsExport";

                break;
            case config('thunderSchool.module.schools.departments'):
                $this->export = "App\Exports\Schools\DepartmentsExport";

                break;
            case config('thunderSchool.module.schools.designations'):
                $this->export = "App\Exports\Schools\DesignationsExport";

                break;
            case config('thunderSchool.module.schools.gradeScales'):
                $this->export = "App\Exports\Schools\GradeScalesExport";

                break;
            case config('thunderSchool.module.schools.standardGrades'):
                $this->export = "App\Exports\StandardGrades\StandardGradesExport";

                break;
            case config('thunderSchool.module.schools.lectures'):
                $this->export = "App\Exports\Schools\LecturesExport";

                break;
            case config('thunderSchool.module.schools.locations'):
                $this->export = "App\Exports\Schools\LocationsExport";

                break;
            case config('thunderSchool.module.schools.materials'):
                $this->export = "App\Exports\Schools\MaterialsExport";

                break;
            case config('thunderSchool.module.schools.schools'):
                $this->export = "App\Exports\Schools\SchoolsExport";

                break;
            case config('thunderSchool.module.schools.sessions'):
                $this->export = "App\Exports\Schools\SchoolSessionsExport";

                break;
            case config('thunderSchool.module.schools.sources'):
                $this->export = "App\Exports\Schools\SchoolSourcesExport";

                break;
            case config('thunderSchool.module.schools.staff'):
                $this->export = "App\Exports\Schools\StaffsExport";

                break;
            case config('thunderSchool.module.schools.subjects'):
                $this->export = "App\Exports\Schools\SubjectsExport";

                break;
            case config('thunderSchool.module.students.admissionInquiry'):
                $this->export = "App\Exports\Students\AdmissionInquiriesExport";

                break;
            case config('thunderSchool.module.students.students'):
                $this->export = "App\Exports\Students\StudentsExport";

                break;
            case config('thunderSchool.module.students.studentRelations'):
                $this->export = "App\Exports\Students\StudentRelationsExport";

                break;
            case config('thunderSchool.module.todos'):
                $this->export = "App\Exports\Todos\TodosExport";

                break;
            case config('thunderSchool.module.assignments'):
                $this->export = "App\Exports\Assignments\AssignmentsExport";

                break;
            case config('thunderSchool.module.roles'):
                $this->export = "App\Exports\Roles\RolesExport";

                break;
            case config('thunderSchool.module.users'):
                $this->export = "App\Exports\Users\UsersExport";

                break;
            case 'student_credentials':
                $this->export = "App\Exports\Students\StudentCredentialsExport";

                break;
            default:
                return response()->json('please select module.');

                break;
        }
        $this->module = ($this->module == 'student_credentials') ? 'students' : $this->module;
        $endDate = (request('end_date')) ? Carbon::parse(request('end_date')) : Carbon::parse(request('start_date'))->addDays(1);
        $this->query = app($this->export)->collection()
            ->when(request('start_date') && $endDate, function ($innerQuery) use ($endDate) {
                return $innerQuery->whereBetween("$this->module.created_at", [
                    Carbon::parse(request('start_date')),
                    $endDate,
                ]);
            })->join('users as creator', function ($join) {
                $join->on("$this->module.creator_id", '=', 'creator.id');
            })->join('users as updater', function ($join) {
                $join->on("$this->module.updater_id", '=', 'updater.id');
            })->addSelect(
                "$this->module.id as ID",
                'creator.first_name as CREATED BY',
                'updater.first_name as UPDATED BY',
                "$this->module.created_at as CREATED AT",
                "$this->module.updated_at as UPDATED AT"
            )->when(request()->filled('school_id'), function ($innerQuery) {
                return $innerQuery->addSelect('schools.name as SCHOOL');
            });
        $this->query = $this->schoolAndRoleFilter()->where("$this->module.deleted_at", '=', null);

        $this->query = app($this->export)->headings($this->query, json_decode(request('columns'), true));

        return $this->query;
    }

    public function schoolAndRoleFilter()
    {
        if (in_array($this->module, config('thunderSchool.except_modules_export'))) {
            return $this->query;
        }
        if ($this->module === 'roles') {
            return $this->query->when(isSchoolAdmin(), function ($innerQuery) {
                return $innerQuery->where('roles.external_id', auth()->user()->school_id)->where('roles.external_type', config('thunderSchool.external.school_admin.external_type'));
            })->when(isDistrictAdmin(), function ($innerQuery) {
                return $innerQuery->where('external_id', auth()->user()->district_id)->where('external_type', config('thunderSchool.external.district_admin.external_type'));
            })->when(isSuperAdmin(), function ($innerQuery) {
                return $innerQuery->where('external_id', config('thunderSchool.external.super_admin.external_id'))->where('external_type', config('thunderSchool.external.super_admin.external_type'));
            });
        } else {
            return $this->query->when(isSchoolAdmin(), function ($innerQuery) {
                return $innerQuery->where('schools.id', auth()->user()->school_id);
            })->when(isDistrictAdmin(), function ($innerQuery) {
                $schoolIds = School::where('district_id', auth()->user()->district_id)->pluck('id')->all();
                $schoolId = request('school_id') ? [request('school_id')] : $schoolIds;

                return $innerQuery->whereIn('schools.id', $schoolId);
            })->when((isSuperAdmin() && request('school_id')), function ($innerQuery) {
                return $innerQuery->where('schools.id', request('school_id'));
            });
        }
    }
}
