<?php

namespace App\import\Events;

use App\Events\Event;
use App\Import\BaseImport;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class EventImport extends BaseImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($events)
    {
        $result = $this->validate('address', $events);

        foreach ($result['success'] as $event) {
            if (isSuperAdmin()) {
                $event['external_id'] = config('thunderSchool.external.super_admin.external_id');
                $event['external_type'] = config('thunderSchool.external.super_admin.external_type');
            } elseif (isDistrictAdmin()) {
                $event['external_id'] = auth()->user()->district_id;
                $event['external_type'] = config('thunderSchool.external.district_admin.external_type');
            } elseif (isSchoolAdmin()) {
                $event['external_id'] = auth()->user()->school_id;
                $event['external_type'] = config('thunderSchool.external.school_admin.external_type');
            } else {
                return response()->json(['error' => 'You are not authorized.'], 400);
            }

            $event['event_access'] = 1;
            $event['user_id'] = auth()->user()->id;

            $events = Event::create(array_merge($event, $this->setSchoolIdAndUuid()));

            $this->importAddress($event, $events->id, Event::class);
        }

        return response()->json(['success' => 'Import Event Data successfully', 'error' => $result['errors']], 200);
    }
}
