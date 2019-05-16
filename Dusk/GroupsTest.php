<?php

namespace Tests\Browser;

use App\Groups\Group;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GroupsTest extends DuskTestCase
{
    use TestingDataSeeder;
    use DatabaseMigrations;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
    }

    /**
     * Test should show groups dashboard.
     *
     * @return void
     */
    public function test_should_show_groups_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@group-card')
                    ->assertSeeIn('@group-card', 'Groups')
                    ->waitFor('@group-vue-table')
                    ->assertPresent('@group-vue-table')
                    ->assertRouteIs('groups.index');
        });
    }

    /**
     * Test should check groups dashboard columns.
     *
     * @return void
     */
    public function test_should_check_dashboard_columns()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@group-vue-table')
                    ->assertSeeIn('@group-vue-table', 'Name')
                    ->assertSeeIn('@group-vue-table', '% Setup')
                    ->assertSeeIn('@group-vue-table', 'Created')
                    ->assertSeeIn('@group-vue-table', 'Updated')
                    ->assertSeeIn('@group-vue-table', 'Actions');
        });
    }

    /**
     * Test should search groups dashboard.
     *
     * @return void
     */
    public function test_should_search_groups_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@group-vue-table')
                    ->type('.table--groups input[type="text"]', 'TestInputValue')
                    ->waitUntilMissing('@groups-table-actions')
                    ->assertDontSeeIn('@group-vue-table', $this->group->name)
                    ->type('.table--groups input[type="text"]', $this->group->name)
                    ->waitFor('@groups-table-actions')
                    ->assertSeeIn('@group-vue-table', $this->group->name);
        });
    }

    /**
     * Test should check groups actions.
     *
     * @return void
     */
    public function test_should_check_groups_dashboard_actions()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@groups-table-actions')
                    ->assertSeeIn('@groups-view-edit', 'View/Edit')
                    ->assertSeeIn('@groups-delete', 'Delete');
        });
    }

    /**
     * Test should check groups setup error symbol on dashboard.
     *
     * @return void
     */
    public function test_should_check_groups_setup_error_symbol_on_dashboard()
    {
        $this->programType->update(['error_notes' => ['Errors present']]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@error-symbol')
                    ->assertPresent('@error-symbol');
        });
    }

    /**
     * Test should navigate groups view.
     *
     * @return void
     */
    public function test_should_navigate_to_group_view()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@groups-view-edit')
                    ->click('@groups-view-edit')
                    ->waitFor('@group-view')
                    ->assertRouteIs('groups.show', $this->group->uuid);
        });
    }

    /**
     * Test should show error for empty inputs when create group.
     *
     * @return void
     */
    public function test_should_show_errors_for_empty_inputs_when_creating_group()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@group-create-button')
                    ->click('@group-create-button')
                    ->whenAvailable('@group-create-modal', function ($modal) {
                        $modal->press('@group-create-submit')
                              ->waitFor('.invalid-feedback')
                              ->assertSee('The name field is required.')
                              ->assertSee('The selected program types must have at least 1 items.');
                    });
        });
    }

    /**
     * Test should show name has more than 100 characters validation error.
     *
     * @return void
     */
    public function test_should_show_the_name_has_more_than_100_characters_validation_error()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@group-create-button')
                    ->click('@group-create-button')
                    ->whenAvailable('@group-create-modal', function ($modal) {
                        $modal->type('@group-create-name', str_random(101))
                              ->press('@group-create-submit')
                              ->waitFor('.invalid-feedback')
                              ->assertSee('The name may not be greater than 100 characters.');
                    });
        });
    }

    /**
     * Test should check action and setup and error status in group view.
     *
     * @return void
     */
    public function test_should_check_action_and_setup_and_error_status_in_group_view()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-view')
                    ->assertPresent('@group-view-actions')
                    ->assertPresent('@group-delete-button')
                    ->assertPresent('@group-progress-bar');
            foreach ($this->group->programTypes as $programType) {
                if ($programType->setup_notes['message'] == 'Setup complete') {
                    $browser->assertSeeIn('@program-type-status', 'Setup complete');
                } elseif ($programType->setup_notes['message'] == 'Setup incomplete') {
                    $browser->assertSeeIn('@program-type-status', 'Setup incomplete');
                } else {
                    $browser->assertSeeIn('@program-type-status', 'Setup not started');
                }
                if ($programType->error_notes == null) {
                    $browser->assertSeeIn('@program-type-status', 'No errors');
                } else {
                    $browser->assertSeeIn('@program-type-status', 'Errors present');
                }
            }
        });
    }

    /**
     * Test should check group view with program type.
     *
     * @return void
     */
    public function test_should_check_group_view_with_program_type()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-card')
                    ->assertSeeIn('@group-card', ucwords($this->group->name))
                    ->waitFor('@group-notes')
                    ->assertSeeIn('@group-notes', $this->group->notes)
                    ->assertPresent('@program-types-card');
            foreach ($this->group->programTypes as $programType) {
                $browser->assertSeeIn('@program-types-card', $programType->code_name);
            }
        });
    }

    /**
     * Test should check empty state view of program type.
     *
     * @return void
     */
    public function test_should_check_empty_state_view_of_program_type()
    {
        $this->programType->update(['code' => 'out_tasked_email', 'program_type_date_range_start' => null]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-card')
                    ->assertSee($this->group->name)
                    ->assertSeeIn('@group-notes', $this->group->notes)
                    ->assertPresent('@program-types-card')
                    ->assertPresent('@group-setup-link')
                    ->assertPresent('@program-type-delete-button')
                    ->assertMissing('@program-type-allocation');
        });
    }

    /**
     * Test should check group view with out_tasked program type.
     *
     * @return void
     */
    public function test_should_check_group_view_with_out_tasked_program_type()
    {
        $this->programType->update(['code' => 'out_tasked_email']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-view')
                    ->assertPresent('@program-types-card')
                    ->assertSeeIn('@program-type-allocation', $this->group->programTypes->first()->partners->sum('partner_allocation'))
                    ->assertMissing('@group-setup-link')
                    ->assertPresent('@partner-details');
            foreach ($this->group->programTypes->first()->partners as $key => $partner) {
                $browser->assertSeeIn('@partner-data' . $key, $partner->ms_accounts_account_synced['info']['name'])
                        ->assertSeeIn('@partner-data' . $key, $partner->partner_allocation)
                        ->assertSeeIn('@partner-data' . $key, $partner->partner_delivery_day)
                        ->assertSeeIn('@partner-data' . $key, $partner->partner_date_shift_days)
                        ->assertSeeIn('@partner-data' . $key, '$' . $partner->partner_cpl);
            }
        });
    }

    /**
     * Test should check group view with in house program type.
     *
     * @return void
     */
    public function test_should_check_group_view_with_in_house_program_type()
    {
        $this->programType->update(['code' => 'in_house_email', 'pacing' => 'Immediate']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-view')
                    ->assertPresent('@group-delete-button')
                    ->assertPresent('@program-types-card');
            foreach ($this->group->programTypes as $programType) {
                $browser->assertSeeIn('@program-types-card', $programType->code_name)
                                ->assertSeeIn('@in-house-date-shift-days', 'Delivery: ' . $programType->in_house_date_shift_days . ' days')
                                ->assertSeeIn('@in-house-cpl', '$' . $programType->in_house_cpl)
                                ->assertSeeIn('@program-type-pacing', $programType->pacing . ' : ' . $programType->pacing_through_days . ' days')
                                ->assertSeeIn('@in-house-delivery-day', $programType->in_house_delivery_day)
                                ->assertSeeIn('@program-type-status', $programType->code_name);
            }
        });
    }

    /**
     * Test should check edit group modal.
     *
     * @return void
     */
    public function test_should_check_edit_group_modal()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-edit-button')
                    ->press('@group-edit-button')
                    ->whenAvailable('@group-edit-modal', function ($modal) {
                        $modal->assertVisible('@modal-content')
                              ->assertInputValue('@group-edit-name', $this->group->name)
                              ->assertInputValue('@group-edit-notes', $this->group->notes)
                              ->assertPresent('@group-edit-close')
                              ->assertPresent('@group-edit-submit');
                        $modal->press('@group-edit-close');
                    })
                    ->waitUntilMissing('@group-edit-modal');
        });
    }

    /**
     * Test should check update group.
     *
     * @return void
     */
    public function test_should_update_group()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-edit-button')
                    ->press('@group-edit-button')
                    ->whenAvailable('@group-edit-modal', function ($modal) {
                        $modal->type('@group-edit-name', $this->createGroupData['name'])
                              ->type('@group-edit-notes', $this->createGroupData['notes'])
                              ->press('@group-edit-submit');
                    })
                    ->waitForReload()
                    ->waitFor('.toast-success')
                    ->assertSee('The group was updated successfully')
                    ->waitFor('@group-view-body')
                    ->assertSee($this->createGroupData['name'])
                    ->assertSee($this->createGroupData['notes']);
        });
    }

    /**
     * Test should give warning on update a group with same data.
     *
     * @return void
     */
    public function test_should_give_warning_on_update_group_with_same_data()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-edit-button')
                    ->press('@group-edit-button')
                    ->whenAvailable('@group-edit-modal', function ($modal) {
                        $modal->press('@group-edit-submit');
                    })
                    ->waitFor('.toast-warning')
                    ->assertSee('Nothing changed in group.');
        });
    }

    /**
     * Test should show errors for empty inputs when updating a group.
     *
     * @return void
     */
    public function test_should_show_errors_for_empty_inputs_when_updating_group()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-edit-button')
                    ->press('@group-edit-button')
                    ->whenAvailable('@group-edit-modal', function ($modal) {
                        $modal->type('@group-edit-name', ' ')
                              ->type('@group-edit-notes', ' ')
                              ->press('@group-edit-submit')
                              ->waitFor('.invalid-feedback')
                              ->assertSee('The name field is required');
                    })
                    ->waitFor('.toast-danger')
                    ->assertSee('Group update was unsuccessful.');
        });
    }

    /**
     * Test should show errors for invalid inputs when updating group.
     *
     * @return void
     */
    public function test_should_show_errors_for_invalid_inputs_when_updating_group()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-edit-button')
                    ->press('@group-edit-button')
                    ->whenAvailable('@group-edit-modal', function ($modal) {
                        $modal->type('@group-edit-name', str_random(101))
                              ->press('@group-edit-submit')
                              ->waitFor('.invalid-feedback')
                              ->assertSee('The name may not be greater than 100 characters.');
                    })
                    ->waitFor('.toast-danger')
                    ->assertSee('Group update was unsuccessful.');
        });
    }

    /**
     * Test should navigate back to group dashboard.
     *
     * @return void
     */
    public function test_should_navigate_back_to_group_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-back-button')
                    ->click('@group-back-button')
                    ->waitFor('@groups-index')
                    ->assertRouteIs('groups.index');
        });
    }

    /**
     * Test should delete a group from groups dashboard.
     *
     * @return void
     */
    public function test_should_delete_group_from_groups_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups')
                    ->waitFor('@groups-delete')
                    ->press('@groups-delete')
                    ->waitFor('.dg-btn--ok')
                    ->press('Continue')
                    ->waitFor('.toast-success')
                    ->assertSee('The group was deleted successfully.');
        });
    }

    /**
     * Test should delete a group from groups view.
     *
     * @return void
     */
    public function test_should_delete_group_from_groups_view()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@group-delete-button')
                    ->click('@group-delete-button')
                    ->waitFor('.dg-btn--ok')
                    ->press('.dg-btn--ok')
                    ->waitForReload()
                    ->waitFor('.toast-success')
                    ->assertSee('The group was deleted successfully.')
                    ->assertRouteIs('groups.index');
        });
    }

    /**
     * Test should Add program-types from group view.
     *
     * @return void
     */
    public function test_should_add_program_types_from_groups_view()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@program-type-add-button')
                    ->click('@program-type-add-button')
                    ->whenAvailable('@program-type-add-modal', function ($modal) {
                        $modal->waitFor('li:first-child input[type="checkbox"]')
                              ->check('li:first-child input[type="checkbox"]')
                              ->select('li:first-child  select')
                              ->waitFor('li:last-child input[type="checkbox"]')
                              ->check('li:last-child input[type="checkbox"]')
                              ->select('li:last-child  select')
                              ->click('@program-type-add-submit');
                    });
            $browser->waitFor('.toast-success')
                    ->assertSee('The program type was created successfully')
                    ->assertRouteIs('groups.setup', $this->group->uuid);
        });
    }

    /**
     * Test should show error when add program-types without select any program-type.
     *
     * @return void
     */
    public function test_should_show_error_when_add_program_types_without_select_any_program_type()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@program-type-add-button')
                    ->click('@program-type-add-button')
                    ->whenAvailable('@program-type-add-modal', function ($modal) {
                        $modal->click('@program-type-add-submit')
                              ->waitFor('.invalid-feedback')
                              ->assertSee('The selected program types must have at least 1 items.');
                    });
        });
    }

    /**
     * Test should redirect to question allocations from group view.
     *
     * @return void
     */
    public function test_should_redirect_to_question_allocations_from_group_view()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@edit-question-allocation')
                    ->click('@edit-question-allocation')
                    ->waitFor('@question-allocation')
                    ->assertRouteIs('question-allocations.index', $this->group->uuid);
        });
    }

    /**
     * Test should redirect to program types view.
     *
     * @return void
     */
    public function test_should_redirect_to_program_types_view()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@sectors-view-edit-button')
                    ->assertPresent('@sectors-view-edit-button')
                    ->click('@sectors-view-edit-button')
                    ->waitFor('@program-type-view')
                    ->assertPresent('@program-type-view');
        });
    }
}
