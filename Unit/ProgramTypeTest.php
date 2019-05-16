<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\TestingDataSeeder;
use App\ProgramTypes\ProgramType;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProgramTypeTest extends TestCase
{
    use TestingDataSeeder, RefreshDatabase;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
        $this->invalidUuid = $this->getUuid();
        $this->statusActive = config('ms_global.status.ACTIVE');
        $this->setSession();
    }

    /**
     * A test should Create new Program Type Successfully.
     */
    public function test_should_create_new_program_type()
    {
        array_set($this->group, 'group_uuid', $this->group->uuid);
        array_set($this->group, 'selected_program_types', [[
            'code'                    => $this->programType->code,
            'ms_questions_group_uuid' => $this->getUuid(),
        ]]);
        $response = $this->post('program-types/' . $this->group->uuid, $this->group->toArray());
        $this->assertDatabaseHas('program_types', [
            'code'         => head($this->group->selected_program_types)['code'],
            'status'       => 'active',
        ]);
    }

    /***
     * A test should return out tasked program type view.
     */
    public function test_should_return_program_type_view_with_out_tasked_program_type()
    {
        $response = $this->get('groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid);
        $response->assertViewIs('programTypes.show')->assertViewHas('programType');
    }

    /***
     * A test should return out tasked program type view.
     */
    public function test_should_return_program_type_view_with_in_house_program_type()
    {
        $this->programType->update(['code' => 'in_house_email']);
        $response = $this->get('groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid);
        $response->assertViewIs('programTypes.show')->assertViewHas('programType');
    }

    /***
     * A test_should return error while given program type uuid not found.
     */
    public function test_should_return_error_while_program_type_uuid_not_found()
    {
        $response = $this->get('groups/' . $this->group->uuid . '/program-types/' . $this->invalidUuid);
        $response->assertNotFound()->assertViewIs('errors.default');
    }

    /**
     * A test should Update Program Type successfully.
     */
    public function test_should_update_program_type()
    {
        $programType = collect(factory(ProgramType::class)->make())
        ->except('code', 'ms_questions_group_uuid', 'ms_questions_group_synced', 'type', 'code_type', 'code_name', 'setup_notes', 'error_notes')->all();

        $response = $this->put('/program-types/' . $this->programType->uuid, $programType);

        $response->assertSuccessful()->assertExactJson([
            'success' => 'The program type was updated successfully.',
        ]);

        $this->assertDatabaseHas('program_types', [
            'program_type_allocation'                       => $programType['program_type_allocation'],
            'in_house_cpl'                                  => $programType['in_house_cpl'],
            'in_house_date_shift_days'                      => $programType['in_house_date_shift_days'],
            'pacing'                                        => $programType['pacing'],
            'questions_with_allocations'                    => json_encode($programType['questions_with_allocations']),
            'program_type_date_range_start'                 => $programType['program_type_date_range_start'],
            'program_type_date_range_end'                   => $programType['program_type_date_range_end'],
        ]);
    }

    /**
     * A test should display nothing changed message while update program type.
     */
    public function test_should_update_program_type_with_no_changes()
    {
        $programType = collect($this->programType)->only(['program_type_allocation', 'in_house_cpl', 'in_house_date_shift_days',
        'in_house_delivery_day', 'pacing', 'pacing_through_days', 'questions_with_allocations', 'program_type_date_range_start', 'program_type_date_range_end', ])->all();

        $response = $this->put('/program-types/' . $this->programType->uuid, $programType);

        $response->assertSuccessful()->assertExactJson([
            'info' => 'Nothing changed in program type.',
        ]);

        $this->assertDatabaseHas('program_types', [
            'program_type_allocation'                       => $programType['program_type_allocation'],
            'in_house_cpl'                                  => $programType['in_house_cpl'],
            'in_house_date_shift_days'                      => $programType['in_house_date_shift_days'],
            'pacing'                                        => $programType['pacing'],
            'questions_with_allocations'                    => json_encode($programType['questions_with_allocations']),
            'program_type_date_range_start'                 => $programType['program_type_date_range_start'],
            'program_type_date_range_end'                   => $programType['program_type_date_range_end'],
        ]);
    }

    /**
     * A test should destroy program type successfully.
     */
    public function test_should_destroy_program_type()
    {
        $programType = ProgramType::with('sectors', 'questionsAllocations')->first();

        $response = $this->delete('/program-types/' . $programType->uuid);

        $response->assertSuccessful()->assertExactJson([
            'success' => 'The program type was deleted successfully.',
        ]);
        $this->assertSoftDeleted('program_types', ['uuid' => $this->programType->uuid]);

        foreach ($programType->sectors  as $sector) {
            $this->assertSoftDeleted('sectors', ['uuid' => $sector->uuid]);
        }

        foreach ($programType->questionsAllocations  as $questionsAllocation) {
            $this->assertSoftDeleted('questions_allocations', ['uuid' => $questionsAllocation->uuid]);
        }
    }

    /**
     * A test should give an error due to invalid uuid while delete program_type.
     */
    public function test_should_give_error_for_invalid_uuid_on_delete_program_type()
    {
        $response = $this->ajaxDelete('/program-types/' . $this->invalidUuid);
        $response->assertNotFound()->assertExactJson([
            'error' => 'Record not found.',
        ]);
        $this->assertDatabaseMissing('program_types', ['uuid' => $this->invalidUuid]);
    }

    /**
     * [NOTE]
     * Do not break url in new line otherwise test case will failed.
     *
     * A test should give success message on create Group.
     */
    public function test_should_give_success_on_create_in_program_type_with_ajax()
    {
        $response = $this->get('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid . '/response?type=success&action=create&module=program type');
        $response->assertSuccessful()->assertExactJson([
            'success' => 'The program type was created successfully.',
        ]);
    }

    /******************************************************************************************************************************
    *                                   Setup-Notes
    *******************************************************************************************************************************/

    /**
     * A test should return setup-complete while create setup notes for out tasked.
     */
    public function test_should_return_setup_complete_while_create_setup_notes_for_out_tasked()
    {
        $this->programType->update(['code' => 'out_tasked_email']);
        $data = head($this->programType->ms_questions_group_synced);
        $data['question'] = head($this->programType->selected_allocations)['question'];
        $this->programType->update(['ms_questions_group_synced' => [$data]]);

        $response = $this->post('program-types/' . $this->programType->id . '/setup-notes/');

        $this->checkDatabaseAssertion();

        $this->assertEquals($this->programType->refresh()->setup_notes, [
            'percentage_complete' => 100,
            'message'             => 'Setup complete',
        ]);
    }

    /**
     * A test should return setup complete while create setup notes for in house.
     */
    public function test_should_return_setup_complete_while_create_setup_notes_for_in_house()
    {
        $this->programType->update(['code' => 'in_house_email']);
        $data = head($this->programType->ms_questions_group_synced);
        $data['question'] = head($this->programType->selected_allocations)['question'];
        $this->programType->update(['ms_questions_group_synced' => [$data]]);

        $response = $this->post('program-types/' . $this->programType->id . '/setup-notes/');

        $this->checkDatabaseAssertion();

        $this->assertEquals($this->programType->refresh()->setup_notes, [
            'percentage_complete' => 100,
            'message'             => 'Setup complete',
            ]);
    }

    /**
     * A test should create setup notes without question allocations for out tasked.
     */
    public function test_should_create_setup_notes_without_question_allocations_for_out_tasked()
    {
        $this->programType->update(['code' => 'out_tasked_email']);
        DB::table('questions_allocations')->delete();
        $response = $this->post('program-types/' . $this->programType->id . '/setup-notes/');

        $this->checkDatabaseAssertion();

        $this->assertEquals($this->programType->refresh()->setup_notes, [
            'percentage_complete' => 75,
            'message'             => 'Setup incomplete',
        ]);
    }

    /**
     * A test should create setup notes without question allocations for in house.
     */
    public function test_should_create_setup_notes_without_question_allocations_for_in_house()
    {
        $this->programType->update(['code' => 'in_house_email']);
        DB::table('questions_allocations')->delete();

        $response = $this->post('program-types/' . $this->programType->id . '/setup-notes/');

        $this->checkDatabaseAssertion();

        $this->assertEquals($this->programType->refresh()->setup_notes, [
            'percentage_complete' => 75,
            'message'             => 'Setup incomplete',
        ]);
    }

    /**
     * A test should create setup notes without sectors and question allocation for out tasked.
     */
    public function test_should_create_setup_notes_without_sectors_and_question_allocation_for_out_tasked()
    {
        $this->programType->update(['code' => 'out_tasked_email']);
        DB::table('sectors')->delete();
        DB::table('questions_allocations')->delete();

        $response = $this->post('program-types/' . $this->programType->id . '/setup-notes/');

        $this->checkDatabaseAssertion();

        $this->assertEquals($this->programType->refresh()->setup_notes, [
            'percentage_complete' => 50,
            'message'             => 'Setup incomplete',
        ]);
    }

    /**
     * A test should create setup notes without sectors and question allocation for in house.
     */
    public function test_should_create_setup_notes_without_sectors_and_question_allocation_for_in_house()
    {
        $this->programType->update(['code' => 'in_house_email']);
        DB::table('sectors')->delete();
        DB::table('questions_allocations')->delete();

        $response = $this->post('program-types/' . $this->programType->id . '/setup-notes/');

        $this->checkDatabaseAssertion();

        $this->assertEquals($this->programType->refresh()->setup_notes, [
            'percentage_complete' => 50,
            'message'             => 'Setup incomplete',
        ]);
    }

    /**
     * A test should create setup notes without partners and question allocation for out tasked.
     */
    public function test_should_create_setup_notes_without_partners_and_question_allocation_for_out_tasked()
    {
        $this->programType->update(['code' => 'out_tasked_email']);
        DB::table('sectors')->delete();
        DB::table('questions_allocations')->delete();
        DB::table('partners')->delete();
        $response = $this->post('program-types/' . $this->programType->id . '/setup-notes/');

        $this->checkDatabaseAssertion();

        $this->assertEquals($this->programType->refresh()->setup_notes, [
            'percentage_complete' => 25,
            'message'             => 'Setup incomplete',
        ]);
    }

    /**
     * A test should return setup not started while create setup notes.
     */
    public function test_should_return_setup_not_started_while_create_setup_notes()
    {
        $this->programType->update(['pacing' => null]);
        $this->programType->update(['code' => 'out_tasked_email']);
        DB::table('sectors')->delete();
        DB::table('questions_allocations')->delete();
        DB::table('partners')->delete();

        $response = $this->post('program-types/' . $this->programType->id . '/setup-notes/');

        $this->assertEquals($this->programType->refresh()->setup_notes, [
            'percentage_complete' => 0,
            'message'             => 'Setup not started',
            ]);
    }

    /**
     * Check database assertion for program types.
     * @param mixed $isPacingNotNull
     */
    protected function checkDatabaseAssertion($isPacingNotNull = true)
    {
        $this->assertDatabaseHas('program_types', [
            'id'                                         => $this->programType->id,
            'program_type_date_range_start'              => $this->programType->program_type_date_range_start,
            'program_type_date_range_end'                => $this->programType->program_type_date_range_end,
            'program_type_allocation'                    => $this->programType->program_type_allocation,
            'pacing'                                     => ($isPacingNotNull) ? $this->programType->pacing : null,
            'setup_notes'                                => json_encode($this->programType->refresh()->setup_notes),
            'status'                                     => $this->statusActive,
        ]);
    }
}
