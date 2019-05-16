<?php

namespace App\Traits;

use App\UserSchools\UserSchool;
use Illuminate\Support\Facades\Route;

trait SchoolFilter
{
    public static function scopeFilterBySchool($query, $schoolIds = [])
    {
        if (!is_array($schoolIds)) {
            $schoolIds = ($schoolIds == null) ? [] : json_decode($schoolIds, true);
        }

        $currentRoute = Route::currentRouteName();
        $column = ($currentRoute == 'school.index') ? 'schools.id' : $query->getModel()->getTable().'.school_id';

        if (isSuperAdmin()) {
            if (!empty($schoolIds)) {
                return $query->whereIn($column, $schoolIds);
            }

            return $query;
        }

        if (isDistrictAdmin()) {
            $userSchoolIds = UserSchool::where('user_id', auth()->user()->id);
            if (!empty($schoolIds)) {
                $userSchoolIds->whereIn('school_id', $schoolIds);
            }
            $userSchoolIds = $userSchoolIds->pluck('school_id')->all();

            return $query->whereIn($column, $userSchoolIds);
        }

        if (isSchoolAdmin()) {
            $schoolId = auth()->user()->school_id;

            return $query->where($column, '=', $schoolId);
        }
    }
}
