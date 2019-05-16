<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProgramTypeTest extends DuskTestCase
{
    use DatabaseMigrations, TestingDataSeeder;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
    }

    /**
     * Test should check program type view with out tasked.
     */
    public function test_should_check_program_type_view_with_out_tasked()
    {
        $this->programType->update(['code' => 'out_tasked_email']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
            ->waitFor('@program-type-view')
            ->assertPresent('@group-view-back-button')
            ->assertSeeIn('@program-type-card', $this->programType->getCodeNameAttribute())
            ->assertSeeIn('@program-type-allocation', $this->programType->program_type_allocation)
            ->assertSeeIn('@program-type-date-range-start', parseDate($this->programType->program_type_date_range_start, 'm/d/Y'))
            ->assertSeeIn('@program-type-date-range-end', parseDate($this->programType->program_type_date_range_end, 'm/d/Y'))
            ->assertPresent('@partner-heading')
            ->assertPresent('@edit-info-button')
            ->assertPresent('@edit-partners-button')
            ->assertPresent('@delete-info-and-sectors')
            ->assertPresent('@sector-dashboard');
            foreach ($this->programType->partners as $partner) {
                $browser->assertSeeIn('@partner-name' . $partner->id, $partner->ms_accounts_account_synced['info']['name'])
                ->assertSeeIn('@partner-allocation' . $partner->id, $partner->partner_allocation)
                ->assertSeeIn('@partner-delivery-day' . $partner->id, $partner->partner_delivery_day)
                ->assertSeeIn('@partner-date-shift-days' . $partner->id, $partner->partner_date_shift_days . ' days')
                ->assertSeeIn('@partner-cpl' . $partner->id, '$' . $partner->partner_cpl);
            }
        });
    }

    /**
     * Test should check program type view with in house.
     */
    public function test_should_check_program_type_view_with_in_house()
    {
        $this->programType->update(['code' => 'in_house_email']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
            ->waitFor('@program-type-view')
            ->assertPresent('@group-view-back-button')
            ->assertSeeIn('@program-type-card', $this->programType->getCodeNameAttribute())
            ->assertSeeIn('@program-type-allocation', $this->programType->program_type_allocation)
            ->assertSeeIn('@program-type-date-range-start', parseDate($this->programType->program_type_date_range_start, 'm/d/Y'))
            ->assertSeeIn('@program-type-date-range-end', parseDate($this->programType->program_type_date_range_end, 'm/d/Y'))
            ->assertSeeIn('@in-house-date-shift-days', 'Delivery: ' . $this->programType->in_house_date_shift_days . ' days')
            ->assertSeeIn('@in-house-cpl', '$' . $this->programType->in_house_cpl)
            ->assertPresent('@edit-info-button')
            ->assertMissing('@edit-partners-button-button')
            ->assertMissing('@partner-data')
            ->assertPresent('@delete-info-and-sectors')
            ->assertSeeIn('@program-type-allocation', $this->programType->program_type_allocation)
            ->assertSeeIn('@in-house-delivery-day', $this->programType->in_house_delivery_day)
            ->assertSeeIn('@in-house-date-shift-days', $this->programType->in_house_date_shift_days)
            ->assertSeeIn('@in-house-cpl', $this->programType->in_house_cpl);
        });
    }

    /**
     * Test should delete program type successfully.
     */
    public function test_should_delete_program_type_successfully()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
            ->waitFor('@delete-info-and-sectors')
            ->press('@delete-info-and-sectors')
            ->waitFor('.dg-btn--ok')
            ->press('Continue')
            ->waitFor('.toast-success')
            ->assertSee('The program type was deleted successfully.');
        });
    }

    /**
     * Test should add program type to group.
     */
    public function test_should_add_program_type_to_group()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@program-type-add-button')
                    ->click('@program-type-add-button')
                    ->whenAvailable('@program-type-add-modal', function ($modal) {
                        $modal->waitFor('@program-type-add-in_house_email')
                              ->check('@program-type-add-in_house_email')
                              ->pause(5000)
                              ->select('@program-type-add-question-group')
                              ->click('@program-type-add-submit');
                    })
                    ->waitFor('@group-setup-card')
                    ->assertRouteIs('groups.show', $this->group->uuid . '/setup');
        });
    }

    /**
     * Test should give error for empty program type when add program type to group.
     */
    public function test_should_show_errors_for_empty_program_type_when_add_program_type_to_group()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@program-type-add-button')
                    ->click('@program-type-add-button')
                    ->whenAvailable('@program-type-add-modal', function ($modal) {
                        $modal->press('@program-type-add-submit')
                              ->waitFor('.invalid-feedback')
                              ->assertSee('The selected program types must have at least 1 items.');
                    });
        });
    }

    /**
     * Test should give error for empty question group when add program type to group.
     */
    public function test_should_show_errors_for_empty_question_group_when_add_program_type_to_group()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid)
                    ->waitFor('@program-type-add-button')
                    ->click('@program-type-add-button')
                    ->whenAvailable('@program-type-add-modal', function ($modal) {
                        $modal->check('@program-type-add-in_house_email')
                              ->press('@program-type-add-submit')
                              ->waitFor('.invalid-feedback')
                              ->assertSee('The selected program types must have at least 1 items.')
                              ->press('@program-type-add-close')
                              ->waitUntilMissing('@program-type-add-modal')
                              ->assertMissing('@program-type-add-modal');
                    });
        });
    }

    /**
     * Test should Update program type with out tasked program type.
     */
    public function test_should_update_program_type_with_out_tasked_program_type()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-info-button')
                    ->click('@edit-info-button')
                    ->whenAvailable('@program-type-edit-modal', function ($modal) {
                        $modal->type('@program-type-edit-allocation', '51')
                            ->click('@program-type-edit-start-date')
                            ->waitFor('@program-type-edit-end-date')
                            ->click('@program-type-edit-end-date')
                            ->radio('@program-type-edit-pacing', 'Normal')
                            ->check('.custom-checkbox')
                            ->press('@program-type-edit-submit');
                    })
                    ->waitFor('.toast-success')
                    ->assertSee('The program type was updated successfully.');
        });
    }

    /**
     * Test should check edit program type modal with out tasked program type.
     */
    public function test_should_check_edit_program_type_modal_with_out_tasked_program_type()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-info-button')
                    ->click('@edit-info-button')
                    ->whenAvailable('@program-type-edit-modal', function ($modal) {
                        $modal->assertVisible('@modal-content')
                              ->assertInputValue('@program-type-edit-allocation', $this->programType->program_type_allocation)
                              ->assertPresent('@program-type-edit-close')
                              ->assertPresent('@program-type-edit-submit');
                        $modal->press('@program-type-edit-close');
                    })
                    ->waitUntilMissing('@program-type-edit-modal');
        });
    }

    /**
     * Test should give warning on update a program type with same data with out tasked program type.
     */
    public function test_should_give_warning_on_update_program_type_with_same_data_with_out_tasked_program_type()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-info-button')
                    ->click('@edit-info-button')
                    ->whenAvailable('@program-type-edit-modal', function ($modal) {
                        $modal->press('@program-type-edit-submit');
                    })
                    ->waitFor('.toast-warning')
                    ->assertSee('Nothing changed in program type.');
        });
    }

    /**
     * Test should show errors for empty inputs when updating a program type with out tasked program type.
     */
    public function test_should_show_errors_for_empty_inputs_when_updating_program_type_with_out_tasked_program_type()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-info-button')
                    ->press('@edit-info-button')
                    ->whenAvailable('@program-type-edit-modal', function ($modal) {
                        $modal->type('@program-type-edit-allocation', str_random(3))
                              ->press('@program-type-edit-submit')
                              ->assertSee('The overall allocation field is required and may only contain numeric characters.');
                    });
        });
    }

    /**
     * Test should Update program type with in house program type.
     */
    public function test_should_update_program_type_with_in_house_program_type()
    {
        $this->programType->update(['code' => 'in_house_teledemand']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-info-button')
                    ->click('@edit-info-button')
                    ->whenAvailable('@program-type-edit-modal', function ($modal) {
                        $modal->type('@program-type-edit-allocation', mt_rand(1, 1000))
                            ->click('@program-type-edit-start-date')
                            ->waitFor('@program-type-edit-end-date')
                            ->click('@program-type-edit-end-date')
                            ->type('@program-type-edit-date-shift', mt_rand(1, 30))
                            ->type('@program-type-edit-cpl', mt_rand(1, 100))
                            ->radio('@program-type-edit-pacing', 'Normal')
                            ->click('.custom-checkbox')
                            ->press('@program-type-edit-submit');
                    })
                    ->waitFor('.toast-success')
                    ->assertSee('The program type was updated successfully.');
        });
    }

    /**
     * Test should check edit program type modal with in house program type.
     */
    public function test_should_check_edit_program_type_modal_with_in_house_program_type()
    {
        $this->programType->update(['code' => 'in_house_teledemand']);
        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-info-button')
                    ->click('@edit-info-button')
                    ->whenAvailable('@program-type-edit-modal', function ($modal) {
                        $modal->assertVisible('@modal-content')
                              ->assertInputValue('@program-type-edit-allocation', $this->programType->program_type_allocation)
                              ->assertSelected('@program-type-edit-delivery-day', $this->programType->in_house_delivery_day)
                              ->assertInputValue('@program-type-edit-date-shift', $this->programType->in_house_date_shift_days)
                              ->assertInputValue('@program-type-edit-cpl', $this->programType->in_house_cpl)
                              ->assertPresent('@program-type-edit-submit')
                              ->assertPresent('@program-type-edit-close')
                              ->press('@program-type-edit-close');
                    });
        });
    }

    /**
     * Test should give warning on update a program type with same data with in house program type.
     */
    public function test_should_give_warning_on_update_program_type_with_same_data_with_in_house_program_type()
    {
        $this->programType->update(['code' => 'in_house_teledemand']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-info-button')
                    ->click('@edit-info-button')
                    ->whenAvailable('@program-type-edit-modal', function ($modal) {
                        $modal->press('@program-type-edit-submit');
                    })
                    ->waitFor('.toast-warning')
                    ->assertSee('Nothing changed in program type.');
        });
    }

    /******************************************************************************************************************************
    *                                   Partner module
    *******************************************************************************************************************************/

    /**
     * Test should Update partner.
     */
    public function test_should_update_partner()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-partners-button')
                    ->click('@edit-partners-button')
                    ->whenAvailable('@partner-edit-modal', function ($modal) {
                        $modal->type('@partner-edit-allocation', mt_rand(1, 1000))
                              ->type('@partner-edit-cpl', mt_rand(1, 100))
                              ->press('@partner-edit-submit');
                    })
                    ->waitFor('.toast-success')
                    ->assertSee('The partner was updated successfully.');
        });
    }

    /**
     * Test should add partner in edit partner modal.
     */
    public function test_should_add_partner_in_edit_partner_modal()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);
        $partnerCount = $this->programType->partners->count() + 1;

        $this->browse(function (Browser $browser) use ($partnerCount) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-partners-button')
                    ->click('@edit-partners-button')
                    ->whenAvailable('@partner-edit-modal', function ($modal) use ($partnerCount) {
                        $modal->waitFor('@add-partner-select')
                             ->select('@add-partner-select')
                              ->waitFor('@account-partners')
                              ->waitFor('.partners:nth-child(' . $partnerCount . ') .row.mb-2 div:nth-child(1) > input')
                              ->type('.partners:nth-child(' . $partnerCount . ') .row.mb-2 div:nth-child(1) > input', '22')
                              ->type('.partners:nth-child(' . $partnerCount . ') .row.mb-2 div:nth-child(2) > input', '12')
                              ->waitFor('.partners:nth-child(' . $partnerCount . ') .row.mb-2 div:nth-child(3) > select')
                              ->select('.partners:nth-child(' . $partnerCount . ') .row.mb-2 div:nth-child(3) > select')
                              ->type('.partners:nth-child(' . $partnerCount . ') .row.mb-2 div:nth-child(4)  input', '2')
                              ->press('@partner-edit-submit');
                    })
                    ->waitFor('.toast-success')
                    ->assertSee('The partner was updated successfully.');
        });
    }

    /**
     * Test should check edit partner modal.
     */
    public function test_should_check_edit_partner_modal()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-partners-button')
                    ->click('@edit-partners-button')
                    ->assertPresent('@partner-edit-modal');
            foreach ($this->programType->partners as $partner) {
                $browser->assertSee($partner->partner_allocation)
                                ->assertPresent('@partner-edit-modal')
                                ->assertSee($partner->partner_cpl)
                                ->assertSee($partner->partner_delivery_day)
                                ->assertSee($partner->partner_date_shift_days);
            }
            $browser->press('@partner-edit-close')
                    ->waitUntilMissing('@partner-edit-modal');
        });
    }

    /**
     * Test should give warning on update a partner with same data.
     */
    public function test_should_give_warning_on_update_partner_with_same_data()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-partners-button')
                    ->click('@edit-partners-button')
                    ->whenAvailable('@partner-edit-modal', function ($modal) {
                        $modal->press('@partner-edit-submit');
                    })
                    ->waitFor('.toast-warning')
                    ->assertSee('Nothing changed in partner.');
        });
    }

    /**
     * Test should show errors for empty inputs when updating a partner.
     */
    public function test_should_show_errors_for_empty_inputs_when_updating_partner()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                    ->waitFor('@edit-partners-button')
                    ->press('@edit-partners-button')
                    ->whenAvailable('@partner-edit-modal', function ($modal) {
                        $modal->waitFor('@add-partner-select')
                              ->select('@add-partner-select')
                              ->type('@partner-edit-allocation', ' ')
                              ->type('@partner-edit-cpl', ' ')
                              ->type('@partner-edit-date-shift', 'a')
                              ->press('@partner-edit-submit')
                              ->waitFor('.text-danger')
                              ->assertSee('The partner allocation field is required and may only contain numeric characters.')
                              ->assertSee('The partner cpl field is required and may only contain numeric characters.')
                              ->assertSee('The partner date shift days field is required and may only contain numeric characters.');
                    });
        });
    }

    /**
     * Test should delete partner successfully.
     */
    public function test_should_delete_partner_successfully()
    {
        $this->programType->update(['code' => 'out_tasked_teledemand']);

        $this->browse(function (Browser $browser) {
            $browser->visit('/groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid)
                ->waitFor('@edit-partners-button')
                ->press('@edit-partners-button')
                ->waitFor('@partner-delete-button')
                ->press('@partner-delete-button')
                ->waitFor('.dg-btn--ok')
                ->press('Continue')
                ->waitFor('.toast-success')
                ->assertSee('The partner was deleted successfully.');
        });
    }
}
