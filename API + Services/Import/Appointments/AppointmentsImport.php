<?php

namespace App\import\Appointments;

use App\Students\Student;
use App\Import\ImportInterface;
use App\Students\StudentParent;
use App\Appointment\Appointment;
use App\Appointment\AppointmentUser;
use App\Import\ImportRepeatedDataTrait;
use App\Import\ValidateImportDataTrait;

class AppointmentsImport implements ImportInterface
{
    use ValidateImportDataTrait, ImportRepeatedDataTrait;

    public function import($appointments)
    {
        $errors = [];
        $result = $this->validate('appointment', $appointments);
        $studentsParents = $this->studentParents();
        $students = $this->students();

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($result['success'] as $appointment) {
                $appointment['uuid'] = generateUuid();
                $appointment['school_id'] = auth()->user()->school_id;
                $appointment['user_id'] = auth()->user()->id;

                $appointmentWithData = $appointment['appointment_with'];

                if ($appointmentWithData == 'parent') {
                    if (array_key_exists($appointment['email'], $studentsParents)) {
                        $appointmentData = Appointment::create($appointment);

                        $data = [
                            'uuid'           => generateUuid(),
                            'appointment_id' => $appointmentData->id,
                            'external_id'    => $studentsParents[$appointment['email']],
                            'external_type'  => StudentParent::class,
                        ];

                        $appointmentUser = AppointmentUser::create($data);
                    }
                } elseif ($appointmentWithData == 'student') {
                    if (array_key_exists($appointment['email'], $students)) {
                        $appointmentData = Appointment::create($appointment);

                        $data = [
                            'uuid'           => generateUuid(),
                            'appointment_id' => $appointmentData->id,
                            'external_id'    => $students[$appointment['email']],
                            'external_type'  => Student::class,
                        ];

                        $appointmentUser = AppointmentUser::create($data);
                    }
                }
            }

            return response()->json(['success' => 'Import Appointment Data successfully'], 200);
        }
    }
}
