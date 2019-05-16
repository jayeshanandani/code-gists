<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Groups\Group;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupTest extends TestCase
{
    use TestingDataSeeder, RefreshDatabase;

    public function setup()
    {
        parent::setUp();
        $this->statusActive = config('ms_global.status.ACTIVE');
        $this->makeTestingData();
        $this->setSession();
        $this->invalidUuid = $this->getUuid();
    }

    /**
     * A test should return group index view.
     */
    public function test_should_return_group_index_view()
    {
        $response = $this->get('groups');
        $response->assertViewIs('groups.index');
    }

    /**
     * A test should Create new Group Successfully.
     */
    public function test_should_create_new_group()
    {
        $response = $this->post('groups', $this->createGroupData);
        $response->assertSuccessful()->assertExactJson([
            'success' => 'The group was created successfully.',
            'uuid'    => Group::orderBy('id', 'desc')->first()->uuid,
        ]);
        $this->assertDatabaseHas('groups', [
            'name'         => $this->createGroupData['name'],
            'notes'        => $this->createGroupData['notes'],
            'status'       => $this->statusActive,
        ]);
        $this->assertDatabaseHas('program_types', [
            'code'         => $this->programType->code,
            'status'       => $this->statusActive,
        ]);
    }

    /**
     * A test should return all groups data for the vue server table.
     */
    public function test_should_get_all_groups_data()
    {
        $jsonStructure = ['uuid', 'created_at', 'updated_at', 'name', 'setup_percentage', 'is_error'];
        $response = $this->get('/groups/all?query=' . $this->group->name . '&limit=10&ascending=1&page=1&byColumn=0');
        $response->assertSuccessful()->assertJsonStructure([
            'data' => ['*' => $jsonStructure],
            'count',
        ]);
    }

    /**
     * A test should get program-types from config.
     */
    public function test_should_get_program_types_from_config()
    {
        $response = $this->ajaxGet('get-program-types');
        $response->assertSuccessful()->assertExactJson(config('ms_global.program_types'));
    }

    /**
     * A test should get remaining program-types for particular group from config.
     */
    public function test_should_get_remaining_program_types_for_group_from_config()
    {
        $response = $this->ajaxGet('get-pending-group-program-types/' . $this->group->uuid);
        $groupProgramTypes = $this->group->with('programTypes')->first()->programTypes->pluck('code')->toArray();
        $exceptedProgramTypes = collect(config('ms_global.program_types'))->except($groupProgramTypes)->all();
        $response->assertSuccessful()->assertExactJson($exceptedProgramTypes);
    }

    /**
     * A test should get question-group from ms-questions.
     */
    public function test_should_get_question_group_from_ms_questions()
    {
        $keys = ['uuid', 'name', 'info', 'decision_tree_workflow', 'tags', 'status', 'created_at', 'updated_at'];
        $response = $this->ajaxGet('get-groups-questions');
        $response->assertSuccessful()->assertJsonStructure([
            '*' => $keys,
        ]);
    }

    /**
     * A test should return group show view.
     */
    public function test_should_return_group_view()
    {
        $response = $this->get('/groups/' . $this->group->uuid);
        $response->assertViewIs('groups.show')->assertViewHas('group');
    }

    /**
     *A test should return error of uuid not found on group view.
     */
    public function test_should_return_error_group_uuid_not_found()
    {
        $response = $this->get('/groups/' . $this->invalidUuid);
        $response->assertNotFound()->assertViewIs('errors.default');
        $this->assertDatabaseMissing('groups', ['uuid' => $this->invalidUuid]);
    }

    /**
     * A test should Update Group Successfully.
     */
    public function test_should_update_group()
    {
        $group = array_except(factory(Group::class)->make()->toArray(), 'uuid');
        $response = $this->put('/groups/' . $this->group->uuid, $group);
        $response->assertSuccessful()->assertExactJson([
            'success' => 'The group was updated successfully.',
        ]);
        $this->assertDatabaseHas('groups', [
            'name'                   => $group['name'],
            'notes'                  => $group['notes'],
            'status'                 => $group['status'],
        ]);
    }

    /**
     * A test should Update Group Successfully with No changes.
     */
    public function test_should_update_group_with_no_changes()
    {
        $response = $this->put('groups/' . $this->group->uuid, $this->group->toArray());
        $response->assertSuccessful()->assertExactJson([
            'info' => 'Nothing changed in group.',
        ]);
        $this->assertDatabaseHas('groups', [
            'name'                   => $this->group->name,
            'status'                 => $this->group->status,
        ]);
    }

    /**
     * A test should give an error for invalid uuid on update group.
     */
    public function test_should_give_an_error_on_invalid_uuid_while_update()
    {
        $group = array_except(factory(Group::class)->make()->toArray(), 'uuid');
        $response = $this->ajaxPut('groups/' . $this->invalidUuid, $group);
        $response->assertNotFound()->assertExactJson([
            'error' => 'Record not found.',
        ]);
        $this->assertDatabaseMissing('groups', ['uuid' =>  $this->invalidUuid]);
    }

    /**
     * A test should destroy group successfully.
     */
    public function test_should_destroy_group()
    {
        $group = Group::with('programTypes.questionsAllocations', 'programTypes.sectors')->first();
        $response = $this->delete('/groups/' . $group->uuid);
        $response->assertSuccessful()->assertExactJson([
            'success' => 'The group was deleted successfully.',
            ]);
        $this->assertSoftDeleted('groups', ['uuid' => $group->uuid]);

        foreach ($group->programTypes as $programType) {
            $this->assertSoftDeleted('program_types', ['uuid' => $programType->uuid]);
            foreach ($programType->sectors as $sector) {
                $this->assertSoftDeleted('sectors', ['uuid' => $sector->uuid]);
            }
            foreach ($programType->questionsAllocations as $questionsAllocation) {
                $this->assertSoftDeleted('questions_allocations', ['uuid' => $questionsAllocation->uuid]);
            }
        }
    }

    /**
     * A test should give an error due to invalid uuid while delete group.
     */
    public function test_should_give_error_for_invalid_uuid_on_delete_group()
    {
        $response = $this->ajaxDelete('/groups/' . $this->invalidUuid);
        $response->assertNotFound()->assertExactJson([
            'error' => 'Record not found.',
        ]);
        $this->assertDatabaseMissing('groups', ['uuid' => $this->invalidUuid]);
    }

    /**
     * A test should give success message on create Group.
     */
    public function test_should_give_success_on_create_in_group_with_ajax()
    {
        $response = $this->get('/groups/response?type=success&action=create');
        $response->assertSuccessful()->assertExactJson([
            'success' => 'The group was created successfully.',
        ]);
    }
}
