<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Sectors\Sector;
use App\Partners\Partner;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupSetupStepTest extends TestCase
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
     * A test should get accounts-partner from ms-accounts.
     */
    public function test_should_get_accounts_partner_from_ms_accounts()
    {
        $keys = ['uuid', 'company_name', 'profiles', 'status', 'created_at', 'updated_at', 'deleted_at', 'templates'];
        $response = $this->ajaxGet('get-accounts-partner');
        $response->assertSuccessful()->assertJsonStructure([$keys]);
    }

    /**
     * A test should return error message if partner not found from ms-accounts.
     */
    public function test_should_return_error_message_if_partners_not_found()
    {
        // To get [] from ms-accounts we need to pass invalid template type
        config(['ms_specific.account.get_partners' => 'api/accounts?template[]=test']);

        $response = $this->ajaxGet('get-accounts-partner');
        $response->assertNotFound()->assertExactJson([
            'status'  => 'error',
            'message' => 'Account record not found.',

        ]);
    }

    /**
     * A test should return group with program types with new add keys.
     * Keys are 'is_completed', 'step', 'is_active'.
     */
    public function test_should_return_group_with_program_type_for_group_setup()
    {
        $this->group->programTypes()->create([
            'uuid'                    => $this->getUuid(),
            'code'                    => 'in_house_teledemand',
            'ms_questions_group_uuid' => $this->getUuid(),
            'status'                  => 'active',
            'created_at'              => Carbon::now(),
        ]);

        $keys = ['is_completed', 'step', 'is_active'];

        $response = $this->get('/groups/' . $this->group->uuid . '/program-type');
        $response->assertSuccessful()->assertJsonStructure(['program_types' => [$keys]]);
    }

    /**
     * A test should return group setup view.
     */
    public function test_should_return_group_setup_view()
    {
        $response = $this->get('/groups/' . $this->group->uuid . '/setup');
        $response->assertViewIs('groups.setup.setup');
    }

    /**
     * A test should create Group Setup for Out-Tasked without question-allocations.
     */
    public function test_should_create_group_setup_for_out_tasked_without_question_allocations()
    {
        $partner = factory(Partner::class)->make();

        $data = array_add($this->programTypeData, 'selected_partners', [$partner->toArray()]);
        data_fill($data, 'selected_partners.*.sectors', [$this->sectorData]);

        $response = $this->post('setups', $data);
        $response->assertSuccessful()->assertExactJson(['Group setup successfully.']);

        $this->assertNotTrue(in_array(head($this->programTypeData['ms_questions_group_synced'])['question'],
                                            $this->programTypeData['questions_with_allocations'])
                            );
        $this->checkDatabaseAssertion(true, false);

        $this->assertDatabaseHas('partners', [
            'program_type_id'                   => $this->programType->id,
            'ms_accounts_account_uuid'          => $partner->ms_accounts_account_uuid,
            'ms_accounts_account_synced'        => json_encode($partner->ms_accounts_account_synced),
            'partner_allocation'                => $partner->partner_allocation,
            'partner_cpl'                       => $partner->partner_cpl,
            'partner_date_shift_days'           => $partner->partner_date_shift_days,
            'partner_delivery_day'              => $partner->partner_delivery_day,
            'status'                            => $this->statusActive,
        ]);
    }

    /**
     * A test should create Group Setup for In-House without question-allocations.
     */
    public function test_should_create_group_setup_for_in_house_without_question_allocations()
    {
        $data = array_add($this->programTypeData, 'sectors', [$this->sectorData]);

        $response = $this->post('setups', $data);
        $response->assertSuccessful()->assertExactJson(['Group setup successfully.']);

        $this->assertNotTrue(in_array(head($this->programTypeData['ms_questions_group_synced'])['question'],
                                $this->programTypeData['questions_with_allocations'])
                            );
        $this->checkDatabaseAssertion(false);
    }

    /**
     * A test should create Group Setup for Out-Tasked with question-allocations.
     */
    public function test_should_create_group_setup_for_out_tasked_with_question_allocations()
    {
        $data = array_add($this->programTypeData, 'selected_partners', [$this->partner1->toArray()]);

        data_fill($data, 'selected_partners.*.sectors', [$this->sectorData]);

        $response = $this->post('setups', $data);

        $response->assertSuccessful()->assertExactJson(['Group setup successfully.']);

        $this->assertDatabaseHas('partners', [
            'program_type_id'                   => $this->programType->id,
            'ms_accounts_account_uuid'          => $this->partner1->ms_accounts_account_uuid,
            'ms_accounts_account_synced'        => json_encode($this->partner1->ms_accounts_account_synced),
            'partner_allocation'                => $this->partner1->partner_allocation,
            'partner_cpl'                       => $this->partner1->partner_cpl,
            'partner_date_shift_days'           => $this->partner1->partner_date_shift_days,
            'partner_delivery_day'              => $this->partner1->partner_delivery_day,
            'status'                            => $this->statusActive,
            ]);
        $this->checkDatabaseAssertion(true);
    }

    /**
     * A test should create Group Setup for In-House with question-allocations.
     */
    public function test_should_create_group_setup_for_in_house_with_question_allocations()
    {
        $data = array_add($this->programTypeData, 'sectors', [$this->sectorData]);

        $response = $this->post('setups', $data);

        $response->assertSuccessful()->assertExactJson(['Group setup successfully.']);
        $this->checkDatabaseAssertion(false);
    }

    /**
     * A test should return program-type error while Group Setup.
     */
    public function test_should_return_program_type_error_while_group_setup()
    {
        $this->programTypeData['allocation'] = 'abc';
        $response = $this->post('setups', $this->programTypeData);
        $response->assertNotFound()->assertExactJson(['Group setup was unsuccessful due to program-type error.']);
    }

    /**
     * A test should return partner error while Group Setup.
     */
    public function test_should_return_partner_error_while_group_setup()
    {
        unset($this->partner1->ms_accounts_account_uuid);
        $data = array_add($this->programTypeData, 'selected_partners', [$this->partner1->toArray()]);

        $response = $this->post('setups', $data);
        $response->assertNotFound()->assertExactJson(['Group setup was unsuccessful due to partner error.']);
    }

    /**
     * A test should return sector error while Group Setup with in-house program type.
     */
    public function test_should_return_sector_error_while_group_setup_with_in_house_program_type()
    {
        $this->sectorData['allocation'] = '';
        unset($this->sectorData['sector_allocation']);

        $data = array_add($this->programTypeData, 'sectors', [$this->sectorData]);

        $response = $this->post('setups', $data);
        $response->assertNotFound()->assertExactJson(['Group setup was unsuccessful due to sectors error.']);
    }

    /**
     * A test should return sector error while Group Setup with out-tasked program type.
     */
    public function test_should_return_sector_error_while_group_setup_with_out_tasked_program_type()
    {
        $this->sectorData['allocation'] = '';
        unset($this->sectorData['sector_allocation']);

        $this->programTypeData['code'] = 'out_tasked_email';
        $data = array_add($this->programTypeData, 'selected_partners', [$this->partner1->toArray()]);
        data_fill($data, 'selected_partners.*.sectors', [$this->sectorData]);

        $response = $this->post('setups', $data);
        $response->assertNotFound()->assertExactJson(['Group setup was unsuccessful due to sectors error.']);
    }

    /**
     * A test should return question-allocations error while Group Setup.
     */
    public function test_should_return_question_allocations_error_while_group_setup()
    {
        $this->programType->update(['question_allocation_min' => null]);
        $this->programTypeData['allocation'] = null;
        $data = array_add($this->programTypeData, 'sectors', [$this->sectorData]);

        $response = $this->post('setups', $data);
        $response->assertNotFound()->assertExactJson(['Group setup was unsuccessful due to question-allocations error.']);
    }

    /**
     * Check common assertion for program type in_house and out_tasked.
     * @param mixed $isOutTask
     * @param mixed $withQuestionAllocation
     */
    protected function checkDatabaseAssertion($isOutTask = false, $withQuestionAllocation = true)
    {
        $this->assertDatabaseHas('program_types', [
            'uuid'                          => $this->programType->uuid,
            'program_type_date_range_start' => $this->programTypeData['start_date'],
            'program_type_date_range_end'   => $this->programTypeData['end_date'],
            'program_type_allocation'       => $this->programTypeData['allocation'],
            'in_house_date_shift_days'      => $this->programTypeData['date_shift_days'],
            'in_house_delivery_day'         => $this->programTypeData['delivery_day'],
            'in_house_cpl'                  => $this->programTypeData['cpl'],
            'pacing'                        => $this->programTypeData['pacing'],
            'pacing_through_days'           => $this->programTypeData['pacing_through_days'],
            'status'                        => $this->statusActive,
        ]);

        $this->assertDatabaseHas('sectors', [
            'program_type_id'                   => $this->programType->id,
            'partner_id'                        => ($isOutTask) ? Partner::latest()->first()->id : null,
            'sector_date_range_start'           => $this->sectorData['start_date'],
            'sector_date_range_end'             => $this->sectorData['end_date'],
            'sector_allocation'                 => $this->sectorData['allocation'],
            'status'                            => $this->statusActive,
        ]);

        if ($withQuestionAllocation) {
            foreach ($this->programTypeData['ms_questions_group_synced'] as $question) {
                if (in_array($question['question'], array_pluck($this->programTypeData['selected_allocations'], 'question'))) {
                    $questionValues = array_where($question['values'], function ($value) {
                        return $value['opt_allowed'];
                    });

                    foreach ($questionValues as $value) {
                        $this->assertDatabaseHas('questions_allocations', [
                            'program_type_id'                         => $this->programType->id,
                            'partner_id'                              => $isOutTask ? Partner::latest()->first()->id : null,
                            'question'                                => $question['question'],
                            'value'                                   => $value['value'],
                            'question_allocation_min'                 => 0,
                            'question_allocation_max'                 => $isOutTask ? $this->partner1->partner_allocation : $this->programTypeData['allocation'],
                            'status'                                  => $this->statusActive,
                        ]);
                    }
                }
            }
        }
    }
}
