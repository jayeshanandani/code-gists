<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Partners\Partner;
use Laravel\Dusk\Browser;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SectorTest extends DuskTestCase
{
    use TestingDataSeeder;
    use DatabaseMigrations;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
    }

    /**
     * Test should show sector dashboard.
     *
     * @return void
     */
    public function test_should_show_sectors_dashboard()
    {
        $this->programType->update(['code' => 'out_tasked_email']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@program-type-view')
                    ->assertPresent('@sector-dashboard')
                    ->assertPresent('@sector-add-button')
                    ->assertPresent('@sector-table')
                    ->assertSeeIn('@sector-table', 'Sectors')
                    ->assertSeeIn('@sector-table', 'Start date')
                    ->assertSeeIn('@sector-table', 'End date')
                    ->assertSeeIn('@sector-table', 'Bus.Days')
                    ->assertSeeIn('@sector-table', 'Allocation')
                    ->assertSeeIn('@sector-table', 'Actions');
        });
    }

    /**
     * Test should show sector dashboard with partner and out tasked program type.
     *
     * @return void
     */
    public function test_should_show_sector_dashboard_with_partner_and_out_tasked_program_type()
    {
        $this->programType->update(['code' => 'out_tasked_email']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@sector-dashboard');

            foreach ($this->partners as $partner) {
                $browser->assertSeeIn('@partner-name' . $partner->id, $partner->ms_accounts_account_synced['info']['name'])
                    ->assertSeeIn('@partner-allocation' . $partner->id, $partner->partner_allocation)
                    ->assertSeeIn('@partner-delivery-day' . $partner->id, $partner->partner_delivery_day)
                    ->assertSeeIn('@partner-date-shift-days' . $partner->id, $partner->partner_date_shift_days)
                    ->assertSeeIn('@partner-cpl' . $partner->id, '$' . $partner->partner_cpl);
            }
        });
    }

    /**
     * Test should show sector dashboard without partner and in house type program type.
     *
     * @return void
     */
    public function test_should_show_sector_dashboard_without_partner_and_in_house_program_type()
    {
        $this->programType->update(['code' => 'in_house_email']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@program-type-view')
                    ->assertSeeIn('@program-type-allocation', $this->programType->program_type_allocation)
                    ->assertSeeIn('@in-house-delivery-day', $this->programType->in_house_delivery_day)
                    ->assertSeeIn('@in-house-date-shift-days', $this->programType->in_house_date_shift_days)
                    ->assertSeeIn('@in-house-cpl', $this->programType->in_house_cpl);
        });
    }

    /**
     * Test should delete sector successfully.
     */
    public function test_should_delete_sector_successfully()
    {
        $this->programType->update(['code' => 'in_house_email']);
        $this->sector->update(['program_type_id' => $this->programType->id, 'partner_id' => null]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@sector-delete-button')
                    ->click('@sector-delete-button')
                    ->waitFor('.dg-btn--ok')
                    ->press('Continue')
                    ->waitFor('.toast-success')
                    ->assertSee('The sector was deleted successfully.');
        });
    }

    /**
     * Test should give error for required inputs when creating a sector.
     *
     * @return void
     */
    public function test_should_check_validation_error_for_empty_inputs()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->assertPresent('@sector-dashboard')
                    ->assertMissing('@add-new-sector')
                    ->click('@sector-add-button')
                   ->driver->executeScript('window.scrollTo(0, 500);');
            $browser->waitFor('@add-new-sector')
                    ->assertPresent('@add-new-sector')
                    ->click('@save-sector')
                    ->waitFor('.toast-danger')
                    ->assertSee('The sector date range start field is required.')
                    ->assertSee('The sector date range end field is required.')
                    ->assertSee('The sector allocation field is required.')
                    ->waitFor('.toast-danger')
                    ->assertSee('Sector creation was unsuccessful.');
        });
    }

    /**
     * Test should add new sector.
     */
    public function test_should_add_new_sector()
    {
        $this->programType->update(['code' => 'in_house_email']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->assertPresent('@sector-dashboard')
                    ->assertMissing('@add-new-sector')
                    ->driver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            $browser->click('@sector-add-button')
                    ->waitFor('@add-new-sector')
                    ->assertPresent('@add-new-sector')
                    ->click('.table tr:nth-child(2) td:nth-child(2)')
                    ->waitFor('.table tr:nth-child(2) td:nth-child(2) .vdp-datepicker__calendar')
                    ->click('.table tr:nth-child(2) td:nth-child(2) .vdp-datepicker__calendar .today') //selects a date within the 'sector_date_range_start' date-picker
                    ->click('.table tr:nth-child(2) td:nth-child(4) input')
                    ->click('.table tr:nth-child(2) td:nth-child(4) .vdp-datepicker__calendar .today + .cell') //selects a date within the 'sector_date_range_end' date-picker
                    ->type('.table tr:nth-child(2) .allocation-input input', 123)
                    ->click('@save-sector')
                    ->waitFor('.toast-success')
                    ->assertSee('The sector was created successfully.');
        });
    }

    /**
     * Test should give warning on update sector with same data.
     */
    public function test_should_give_warning_on_update_a_sector_with_same_data()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-sector-button')
                    ->click('@edit-sector-button')
                    ->click('@update-sector-button')
                    ->waitFor('.toast-warning')
                    ->assertSee('Nothing changed in sector.');
        });
    }

    /**
     * Test should update a sector.
     */
    public function test_should_update_a_sector()
    {
        $this->programType->update(['code' => 'in_house_email']);
        $this->sector->update(['program_type_id' => $this->programType->id]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-sector-button')
                    ->click('@edit-sector-button')
                    ->click('.table tr:nth-child(1) td:nth-child(2) input')
                    ->waitFor('.table tr:nth-child(1) td:nth-child(2) .vdp-datepicker__calendar')
                    ->driver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            $browser->click('.table tr:nth-child(1) td:nth-child(2) .vdp-datepicker__calendar .today + .cell') //selects a date within the 'sector_date_range_start' date-picker
                    ->click('.table tr:nth-child(1) td:nth-child(4) input')
                    ->click('.table tr:nth-child(1) td:nth-child(4) .vdp-datepicker__calendar span.cell:not(.disabled)') //selects a date within the 'sector_date_range_end' date-picker
                    ->type('@allocation-input', 123)
                    ->waitFor('@update-sector-button')
                    ->click('@update-sector-button')
                    ->waitFor('.toast-success')
                    ->assertSee('The sector was updated successfully.');
        });
    }

    /**
     * Test should check sector update in cancel button.
     */
    public function test_should_check_sector_update_in_cancel_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-sector-button')
                    ->click('@edit-sector-button')
                    ->waitForText('Cancel')
                    ->press('Cancel')
                    ->assertDontSee('Cancel');
        });
    }
}
