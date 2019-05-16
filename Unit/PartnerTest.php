<?php

namespace Tests\Unit;

use Tests\TestCase;
use GuzzleHttp\Client;
use App\Partners\Partner;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartnerTest extends TestCase
{
    use TestingDataSeeder,RefreshDatabase;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
        $this->invalidUuid = $this->getUuid();
        $this->setSession();
    }

    /**
     * A test should get remaining accounts partners.
     */
    public function test_should_get_remaining_accounts_partners()
    {
        $client = new Client();

        $existingPartner = $this->programType->partners->pluck('ms_accounts_account_uuid')->toArray();
        $url = config('ms_global.ms_urls.' . config('app.env') . '.accounts') . config('ms_specific.account.get_partners');
        $partners = $client->request('GET', $url, [
            'http_errors' => false,
            'headers'     => [
                'Accept'           => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'X-API-KEY'        => env('MS_API_KEY'),
            ],
            ]);
        $dbPartnerName = collect(json_decode($partners->getBody()->getContents(), true)['data'])
                        ->whereNotIn('uuid', $existingPartner)->pluck('templates.partner.info.name')->all();

        $response = $this->ajaxGet('get-pending-accounts-partner?programTypeUuid=' . $this->programType->uuid);
        $responsePartnerName = collect($response->getData())->pluck('templates.partner.info.name')->all();

        $response->assertSuccessful();
        $this->assertEquals($dbPartnerName, $responsePartnerName);
    }

    /**
     * A test should Update Partner Successfully.
     */
    public function test_should_update_partner()
    {
        $partner = $this->partner1->toArray();
        $partner['partner_allocation'] = 126;
        $response = $this->put('groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid . '/partner', [$partner]);

        $response->assertSuccessful()->assertExactJson([
            'success' => 'The partner was updated successfully.',
        ]);
        $this->assertDatabaseHas('partners', [
            'partner_allocation'   => $partner['partner_allocation'],
        ]);
    }

    /**
     * A test should Update Program type with new partner Successfully.
     */
    public function test_should_update_program_type_with_new_partner()
    {
        $partner = $this->partner1->toArray();
        $partner['uuid'] = $this->invalidUuid;
        $response = $this->put('groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid . '/partner', [$partner]);

        $response->assertSuccessful()->assertExactJson([
            'success' => 'The partner was updated successfully.',
        ]);
        $this->assertDatabaseHas('partners', [
            'partner_allocation'                   => $partner['partner_allocation'],
        ]);
    }

    /**
     * A test should give nothing change message on partner update.
     */
    public function test_should_give_nothing_changed_message_on_update_partner()
    {
        $response = $this->put('groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid . '/partner', $this->partners->toArray());

        $response->assertSuccessful()->assertExactJson([
            'info' => 'Nothing changed in partner.',
        ]);
    }

    /**
     * A test should destroy partner successfully.
     */
    public function test_should_destroy_partner()
    {
        $partner = $this->partner1->load('sectors', 'questionsAllocations');

        $this->programType->update(['code' => 'out_tasked_email']);
        $this->sector->update(['partner_id' => $partner->id]);

        $response = $this->delete('groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid . '/partners/' . $partner->uuid);

        $response->assertSuccessful()->assertExactJson([
            'success' => 'The partner was deleted successfully.',
        ]);
        $this->assertSoftDeleted('partners', ['uuid' => $partner->uuid]);

        foreach ($partner->sectors as $sector) {
            $this->assertSoftDeleted('sectors', ['uuid' => $sector->uuid]);
        }

        foreach ($partner->questionsAllocations as $questionsAllocation) {
            $this->assertSoftDeleted('questions_allocations', ['uuid' => $questionsAllocation->uuid]);
        }
    }

    /**
     * A test should give an error due to invalid uuid while delete partner.
     */
    public function test_should_give_error_for_invalid_uuid_on_delete_partner()
    {
        $response = $this->ajaxDelete('groups/' . $this->group->uuid . '/program-types/' . $this->programType->uuid . '/partners/' . $this->invalidUuid);
        $response->assertNotFound()->assertExactJson([
            'error' => 'Record not found.',
        ]);
        $this->assertDatabaseMissing('partners', ['uuid' => $this->invalidUuid]);
    }
}
