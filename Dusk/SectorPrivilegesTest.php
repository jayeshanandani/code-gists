<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SectorPrivilegesTest extends DuskTestCase
{
    use DatabaseMigrations, TestingDataSeeder;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
    }

    /**
     * Test should missing sector when not given sector read permission.
     */
    public function test_should_missing_sector_on_program_types_view()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'sectors__read');
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->assertDontSeeIn('@program-type-view-body', 'Sectors')
                    ->assertMissing('@sector-dashboard');
        });
    }

    /**
     * Test should check add sector button not visible on program type view.
     *
     * @return void
     */
    public function test_should_check_add_sector_button_not_visible_on_program_type_view()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'sectors__create');
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->assertDontSeeIn('@card-title', 'Add Sector')
                    ->assertMissing('@sector-add-button');
        });
    }

    /**
     * Test should check sector edit button not visible.
     */
    public function test_should_check_sector_edit_button_not_visible()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'sectors__update');
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
            ->waitFor('@program-type-view')->pause(5000)
            ->assertMissing('@edit-sector-button')
            ->assertDontSeeIn('@sector-dashboard', 'Edit');
        });
    }

    /**
     * Test should check sector delete button not visible.
     */
    public function test_should_check_sector_delete_button_not_visible()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'sectors__delete');
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
            ->waitFor('@program-type-view')
            ->assertMissing('@sector-delete-button')
            ->assertDontSeeIn('@sector-dashboard', 'Delete');
        });
    }

    /**
     * A test should display access denied page on group setup.
     */
    public function test_should_access_denied_on_group_setup()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'groups__create');
            $browser->visit('/groups/' . $this->group->uuid . '/setup')
                    ->assertMissing('@group-setup-card')
                    ->assertSee('403')
                    ->assertSee('Access Denied');
        });
    }
}
