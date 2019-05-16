<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PartnerPrivilegesTest extends DuskTestCase
{
    use DatabaseMigrations, TestingDataSeeder;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
    }

    /**
     * Test should check partner edit button not visible.
     */
    public function test_should_check_partner_edit_button_not_visible()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);

        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'partners__update');
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
            ->assertMissing('@edit-partners-button');
        });
    }

    /**
     * Test should check partner delete button not visible.
     */
    public function test_should_check_partner_delete_button_not_visible()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);

        $this->browse(function (Browser $browser) {
            $this->setSession(null, 'partners__delete');
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-partners-button')
                    ->click('@edit-partners-button')
                    ->whenAvailable('@partner-edit-modal', function ($modal) {
                        $modal->assertMissing('@partner-delete');
                    });
        });
    }
}
