<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProgramTypePrivilegesTest extends DuskTestCase
{
    use DatabaseMigrations, TestingDataSeeder;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
    }

    /**
     * Test should missing program type when not given program type read permission.
     */
    public function test_should_missing_program_types_on_group_view()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'program_types__read');
            $browser->visit('/groups/' . $this->group->uuid)
                    ->assertDontSeeIn('@group-view-body', $this->programType->code_name)
                    ->assertMissing('@program-types-card');
        });
    }

    /**
     * Test should missing program type action and status when not given program type read permission.
     */
    public function test_should_missing_program_types_action_and_status_on_group_view()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'program_types__read');
            $browser->visit('/groups/' . $this->group->uuid)
                    ->assertDontSeeIn('@group-view-body', $this->programType->code_name)
                    ->assertMissing('@program-type-status');
        });
    }

    /**
     * Test should check add program type button not visible on group view.
     */
    public function test_should_check_add_program_type_button_not_visible_on_group_view()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'program_types__create');
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-view')
                    ->assertMissing('@program-type-add-button');
        });
    }

    /**
     * Test should check edit sector button not visible on group view.
     */
    public function test_should_check_edit_sector_button_not_visible_on_group_view()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'program_types__update');
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-view')
                    ->assertSeeIn('@sectors-view-edit-button', 'View Sectors')
                    ->assertDontSeeIn('@sectors-view-edit-button', '/Edit');
        });
    }

    /**
     * Test should check program type delete button not visible.
     */
    public function test_should_check_program_type_delete_button_not_visible()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'program_types__delete');
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
            ->waitFor('@program-type-view')
            ->assertMissing('@delete-info-and-sectors')
            ->assertDontSeeIn('@program-type-view', 'Delete Info & Sectors');
        });
    }

    /**
     * Test should check program type edit button not visible.
     */
    public function test_should_check_program_type_edit_button_not_visible()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'program_types__update');
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
            ->waitFor('@program-type-view')
            ->assertMissing('@edit-info')
            ->assertDontSeeIn('@program-type-view', 'Edit Info');
        });
    }
}
