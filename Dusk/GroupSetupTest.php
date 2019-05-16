<?php

namespace Tests\Browser;

use App\Groups\Group;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GroupSetupTest extends DuskTestCase
{
    use TestingDataSeeder;
    use DatabaseMigrations;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
    }

    public function create_group_with_in_house_program_type()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@group-create-button')
                    ->click('@group-create-button')
                    ->whenAvailable('@group-create-modal', function ($modal) {
                        $modal->type('@group-create-name', $this->createGroupData['name'])
                              ->type('@group-create-notes', $this->createGroupData['notes'])
                              ->check('@program-type-add-in_house_email')
                              ->pause(5000) // waits for questionGroups to appear from ms-questions API
                              ->waitFor('@program-type-add-question-group')
                              ->select('@program-type-add-question-group')
                              ->click('@group-create-submit');
                    });
        });
    }

    /**
     * Test should create a group with out-tasked program type.
     *
     * @return void
     */
    public function create_group_with_out_tasked_program_type()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@group-create-button')
                    ->click('@group-create-button')
                    ->whenAvailable('@group-create-modal', function ($modal) {
                        $modal->type('@group-create-name', $this->createGroupData['name'])
                              ->type('@group-create-notes', $this->createGroupData['notes'])
                              ->check('@program-type-add-out_tasked_teledemand')
                              ->pause(5000) // waits for questionGroups to appear from ms-questions API
                              ->waitFor('@program-type-add-question-group')
                              ->select('@program-type-add-question-group')
                              ->click('@group-create-submit');
                    });
        });
    }

    /**
     * Test should create a group with in house program type.
     *
     * @return void
     */
    public function test_should_create_group_with_in_house_program_type()
    {
        $this->browse(function (Browser $browser) {
            $this->create_group_with_in_house_program_type();
            $browser->waitFor('.toast-success')
                    ->assertSee('The group was created successfully.')
                    ->waitFor('@group-setup-card')
                    ->waitForText('Setup Group: ' . $this->createGroupData['name'])
                    ->type('@program-type-setup-allocation', mt_rand(1, 1000))
                    ->click('.date-range div:nth-child(1) .vdp-datepicker input')
                    ->click('.date-range div:nth-child(1) .vdp-datepicker__calendar .today')
                    ->click('.date-range div:nth-child(3) .vdp-datepicker input')
                    ->click('.date-range div:nth-child(3)  .vdp-datepicker__calendar .next')
                    ->click('.date-range div:nth-child(3)  .vdp-datepicker__calendar .cell.weekend')
                    ->type('@program-type-setup-date-shift', 1)
                    ->select('@program-type-setup-day')
                    ->type('@program-type-setup-cpl', mt_rand(1, 200))
                    ->radio('@program-type-setup-pacing', 'Normal')
                    ->driver->executeScript('window.scrollTo(0, 500);');
            $browser->press('Save & Continue')
                    ->waitFor('@weekly-sectors')
                    ->radio('@weekly-sectors', 'weekly')
                    ->waitUntilMissing('.toast-success')
                    ->assertMissing('.toast-success')
                    ->waitFor('@save-and-continue-button')
                    ->click('@save-and-continue-button');
        });
    }

    /**
     * Test should create a group with out-tasked program type.
     *
     * @return void
     */
    public function test_should_create_group_with_out_tasked_program_type()
    {
        $this->browse(function (Browser $browser) {
            $this->create_group_with_out_tasked_program_type();
            $browser->waitFor('.toast-success')
                    ->assertSee('The group was created successfully.')
                    ->waitFor('@program-type-setup-allocation')
                    ->type('@program-type-setup-allocation', mt_rand(1, 1000))
                    ->click('.date-range div:nth-child(1) .vdp-datepicker input')
                    ->click('.date-range div:nth-child(1) .vdp-datepicker__calendar .today')
                    ->click('.date-range div:nth-child(3) .vdp-datepicker input')
                    ->click('.date-range div:nth-child(3)  .vdp-datepicker__calendar .next')
                    ->click('.date-range div:nth-child(3)  .vdp-datepicker__calendar .cell.weekend')
                    ->waitFor('@add-partner-select')
                    ->pause(5000)
                    ->select('@add-partner-select')
                    ->waitFor('.partners')
                    ->type('.partners div:nth-child(1) input', mt_rand(1, 100))
                    ->type('.partners div:nth-child(2)  input', mt_rand(1, 50))
                    ->waitFor('.partners div:nth-child(3)  select')
                    ->select('.partners div:nth-child(3)  select')
                    ->type('.partners div:nth-child(4)   input', mt_rand(1, 10))
                    ->driver->executeScript('window.scrollTo(0, 500);');
            $browser->radio('@program-type-setup-pacing', 'Normal')
                    ->press('Save & Continue')
                    ->waitForText('PROGRAM DATE RANGE')
                    ->radio('@out-tasked-weekly-sectors', 'weekly')
                    ->waitUntilMissing('.toast-success')
                    ->assertMissing('.toast-success')
                    ->waitFor('@save-and-continue-button')
                    ->click('@save-and-continue-button');
        });
    }

    /**
     *  Test should give error for required inputs when creating a group setup.
     */
    public function test_should_give_error_for_required_inputs_when_creating_a_group_setup()
    {
        $this->browse(function (Browser $browser) {
            $this->create_group_with_in_house_program_type();
            $browser->waitFor('.toast-success')
                    ->assertSee('The group was created successfully.')
                    ->waitFor('@program-type-setup-allocation')
                    ->driver->executeScript('window.scrollTo(0, 500);');
            $browser->press('Save & Continue')
                    ->assertSee('The allocation field is required.')
                    ->assertSee('The start date field is required.')
                    ->assertSee('The end date field is required.')
                    ->assertSee('The date shift field is required.')
                    ->assertSee('The delivery day field is required.')
                    ->assertSee('The cpl field is required.')
                    ->assertSee('The pacing field is required.');
        });
    }

    /**
     *  Test should give error for enter character in inputs when creating a group setup.
     */
    public function test_should_give_error_for_enter_invalid_data_in_inputs_when_creating_a_group_setup()
    {
        $this->browse(function (Browser $browser) {
            $this->create_group_with_in_house_program_type();
            $browser->waitFor('.toast-success')
                    ->assertSee('The group was created successfully.')
                    ->waitFor('@program-type-setup-allocation')
                    ->type('@program-type-setup-allocation', str_random(3))
                    ->type('@program-type-setup-date-shift', str_random(3))
                    ->type('@program-type-setup-cpl', str_random(3))
                    ->driver->executeScript('window.scrollTo(0, 500);');
            $browser->waitFor('@program-type-setup-immediate')
                    ->check('@program-type-setup-immediate')
                    ->type('@program-type-setup-days', str_random(3))
                    ->assertSee('The allocation field may only contain numeric characters.')
                    ->assertSee('The date shift field may only contain numeric characters.')
                    ->assertSee('The cpl field must be numeric and may contain decimal points.')
                    ->assertSee('The pacing days field may only contain numeric characters.');
        });
    }
}
