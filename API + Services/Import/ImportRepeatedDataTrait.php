<?php

namespace App\Import;

use App\Roles\Role;
use App\Users\User;
use App\Events\Event;
use App\Grades\Grade;
use App\Masters\City;
use App\Masters\State;
use App\Masters\Country;
use App\Schools\Subject;
use App\Schools\Location;
use App\Students\Student;
use App\Schools\ClassRoom;
use App\Schools\Department;
use App\Schools\Designation;
use App\Schools\SubjectType;
use App\Schools\SchoolSource;
use App\Schools\SchoolSession;
use App\Students\StudentParent;
use App\Students\StudentRelation;
use App\Students\AdmissionInquiry;
use App\StandardGrades\StandardGrade;
use App\StandardGrades\StandardGradeMap;

trait ImportRepeatedDataTrait
{
    public function grades()
    {
        return Grade::where('school_id', auth()->user()->school_id)->pluck('id', 'name')->all();
    }

    public function standardGrades()
    {
        if (isSuperAdmin()) {
            $standardGrades = StandardGrade::where('external_id', -1)->where('external_type', config('thunderSchool.standard_grade.super_admin'))->latest()->get();
        } elseif (isDistrictAdmin()) {
            $standardGrades = StandardGrade::where('external_id', auth()->user()->district_id)->where('external_type', config('thunderSchool.standard_grade.district_admin'))->latest()->get();
        } elseif (isSchoolAdmin()) {
            $standardGrades = StandardGradeMap::where('school_id', auth()->user()->school_id)->with('standardGrade:id,uuid,name,position')->latest()->get();
            $standardGrades = array_values($standardGrades->pluck('standardGrade')->sortBy('position')->filter()->all());
        }

        return collect($standardGrades)->pluck('id', 'name')->all();
    }

    public function schoolSessions()
    {
        return SchoolSession::where('school_id', auth()->user()->school_id)->pluck('id', 'name')->all();
    }

    public function schoolDepartments()
    {
        return Department::where('school_id', auth()->user()->school_id)->pluck('id', 'name')->all();
    }

    public function schoolSubjects()
    {
        return Subject::where('school_id', auth()->user()->school_id)->pluck('id', 'name')->all();
    }

    public function subjectTypes()
    {
        return SubjectType::where('school_id', auth()->user()->school_id)->pluck('id', 'name')->all();
    }

    public function classRooms()
    {
        return ClassRoom::where('school_id', auth()->user()->school_id)->pluck('id', 'name')->all();
    }

    public function schoolLocations()
    {
        return Location::where('school_id', auth()->user()->school_id)->pluck('id', 'title')->all();
    }

    public function designations()
    {
        return Designation::where('school_id', auth()->user()->school_id)->pluck('id', 'name')->all();
    }

    public function schoolSources()
    {
        return SchoolSource::where('school_id', auth()->user()->school_id)->pluck('id', 'title')->all();
    }

    public function studentRelations()
    {
        return StudentRelation::pluck('id', 'name')->all();
    }

    public function students()
    {
        return Student::pluck('id', 'email')->all();
    }

    public function studentParents()
    {
        return StudentParent::pluck('id', 'email')->all();
    }

    public function events()
    {
        return Event::where('school_id', auth()->user()->school_id)->pluck('id', 'title')->all();
    }

    public function users()
    {
        return User::pluck('first_name', 'email')->all();
    }

    public function roles()
    {
        return Role::pluck('id', 'name')->all();
    }

    public function states()
    {
        return  State::pluck('id', 'name')->all();
    }

    public function cities()
    {
        return  City::pluck('id', 'name')->all();
    }

    public function countries()
    {
        return  Country::pluck('id', 'name')->all();
    }

    public function admissionInquiries()
    {
        return  AdmissionInquiry::pluck('id', 'email')->all();
    }

    public function getUserStaff($email)
    {
        return  User::whereEmail($email)->with('staff')->first();
    }

    public function classRoomUsers()
    {
        return ClassRoom::where('school_id', auth()->user()->school_id)->pluck('user_id', 'name')->all();
    }

    public function requiredMessage($value)
    {
        return  $value.' '.'is required.';
    }
}
