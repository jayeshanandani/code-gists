<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GroupPrivilegesTest extends DuskTestCase
{
    use DatabaseMigrations, TestingDataSeeder;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
    }

    /**
     * A test should display access denied page on group index.
     */
    public function test_should_access_denied_on_group_index()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'groups__read');
            $browser->visit('/groups')
                    ->assertMissing('@groups-index')
                    ->assertSee('403')
                    ->assertSee('Access Denied');
        });
    }

    /**
     * A test should display access denied page on group show.
     */
    public function test_should_access_denied_on_group_show()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'groups__read');
            $browser->visit('/groups/' . $this->group->uuid)
                    ->assertMissing('@groups-index')
                    ->assertSee('403')
                    ->assertSee('Access Denied');
        });
    }

    /**
     * A test should check create group button not visible.
     */
    public function test_should_check_create_group_button_not_visible()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'groups__create');
            $browser->visit('/groups')
                    ->waitFor('.card-header')
                    ->assertPresent('.card-header')
                    ->assertMissing('@group-create-button')
                    ->assertDontSeeIn('@groups-index', 'Create New group');
        });
    }

    /**
     * A test should check edit group button not visible.
     */
    public function test_should_check_edit_group_button_not_visible()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'groups__update');
            $browser->visit('/groups/')
                    ->waitFor('@groups-table-actions')
                    ->assertSeeIn('@groups-table-actions', 'View')
                    ->assertDontSeeIn('@groups-table-actions', 'View/Edit')
                    ->visit('/groups/' . $this->group->uuid)
                    ->waitFor('.card-body')
                    ->assertMissing('@group-edit-button')
                    ->assertDontSee('Edit group');
        });
    }

    /**
     * A test should check delete group button not visible.
     */
    public function test_should_check_delete_group_button_not_visible()
    {
        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'groups__delete');
            $browser->visit('/groups/')
                    ->waitFor('@groups-table-actions')
                    ->assertMissing('@groups-delete')
                    ->visit('/groups/' . $this->group->uuid)
                    ->waitFor('.card-body')
                    ->assertMissing('@group-delete-button')
                    ->assertDontSee('Delete Group');
        });
    }
}
