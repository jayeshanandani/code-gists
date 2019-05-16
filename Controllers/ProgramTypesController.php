<?php

namespace App\Http\Controllers\ProgramTypes;

use App\Groups\Group;
use App\Services\Guzzle;
use App\Partners\Partner;
use App\Events\SetupNotes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\ProgramTypes\ProgramType;
use Illuminate\Support\Facades\DB;
use App\Traits\CalculateSectorData;
use Illuminate\Support\Facades\App;
use Illuminate\Database\QueryException;
use App\Http\Controllers\BaseController;
use App\QuestionsAllocations\QuestionAllocation;
use App\Http\Requests\ProgramTypes\ProgramTypeRequest;

class ProgramTypesController extends BaseController
{
    use CalculateSectorData;

    protected $model;

    protected $group;

    protected $partner;

    protected $guzzle;

    protected $questionAllocation;

    public function __construct(ProgramType $programType, Group $group, Partner $partner, Guzzle $guzzle, QuestionAllocation $questionAllocation)
    {
        parent::__construct();
        $this->model = $programType;
        $this->group = $group;
        $this->partner = $partner;
        $this->guzzle = $guzzle;
        $this->questionAllocation = $questionAllocation;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  uuid                      $groupUuid
     * @return \Illuminate\Http\Response
     */
    public function store(ProgramTypeRequest $request, $groupUuid)
    {
        validateAccess('program_types__create');
        $group = $this->group->whereUuid($groupUuid)->firstOrFail();

        foreach ($request->selected_program_types as $programType) {
            $group->programTypes()->create([
                'uuid'                    => $this->getUuid(),
                'code'                    => $programType['code'],
                'ms_questions_group_uuid' => $programType['ms_questions_group_uuid'],
                'status'                  => $this->statusActive,
            ]);
        }

        return response()->jsonSuccess($this->model->module, $this->actionSave, ['uuid' => $group->uuid]);
    }

    /**
     * Display specific program type.
     *
     * @param uuid $groupUuid
     * @param uuid $programTypeUuid
     *
     * @return view
     */
    public function programTypeShow($groupUuid, $programTypeUuid)
    {
        validateAccess('program_types__read');

        $programType = $this->model->whereUuid($programTypeUuid)->with('partners.sectors', 'sectors', 'group')->firstOrFail();

        if ($programType->code_type === 'out_tasked') {
            $programType->partners->each(function ($partner) {
                $this->calculateBusinessDays($partner->sectors);
            });
        } else {
            $this->calculateBusinessDays($programType->sectors);
        }

        data_fill($programType, 'sectors.*.edit', false);
        data_fill($programType, 'partners.*.sectors.*.edit', false);

        return view('programTypes.show', compact('programType', 'groupUuid'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  uuid                      $uuid
     * @return \Illuminate\Http\Response
     */
    public function update($uuid)
    {
        validateAccess('program_types__update');
        $programType = $this->model->whereUuid($uuid)->firstOrFail();
        $programType->fill(request()->except('uuid'))->save();

        if (empty($programType->getChanges())) {
            return response()->jsonInfo($this->model->module, $this->notChanged);
        }

        return response()->jsonSuccess($this->model->module, $this->actionUpdate);
    }

    /**
     * Update program types question group sync column.
     *
     * @param  uuid                      $groupUuid
     * @return \Illuminate\Http\Response
     */
    public function updateProgramTypeQuestionSync($groupUuid)
    {
        $url = config('ms_global.ms_urls.' . config('app.env') . '.questions') . 'api/groups/' . $groupUuid . '?values=1&questions=1';
        $response = $this->guzzle->get($url);

        if (empty($response['data']) || !empty($response['data']['error'])) {
            return $this->prepareResponse('Question-Group Record not found.', $this->statusError, Response::HTTP_NOT_FOUND);
        }

        $programTypes = $this->model->where('ms_questions_group_uuid', $groupUuid)->get();
        DB::beginTransaction();

        if ($programTypes->count()) {
            try {
                $programTypes->map(function ($programTpe) use ($response) {
                    $programTpe->update([
                            'ms_questions_group_synced'  => $response['data']['questions'],
                        ]);
                });
            } catch (QueryException $e) {
                DB::rollBack();

                return $this->prepareResponse('Program type was update unsuccessful.', $this->statusError, Response::HTTP_NOT_FOUND);
            }

            $response = $this->updateQuestionsWithAllocations($programTypes);

            if ($response['status'] === $this->statusError) {
                return $this->prepareResponse('Question with allocation update was unsuccessful.', $this->statusError, Response::HTTP_NOT_FOUND);
            }
            DB::commit();

            return $this->prepareResponse('Program type was update successfully.', $this->statusSuccess, Response::HTTP_OK);
        } else {
            return $this->prepareResponse('Program type was update unsuccessful.', $this->statusError, Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  uuid                      $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        validateAccess('program_types__delete');

        $programType = $this->model->whereUuid($uuid)->firstOrFail();

        $programType->delete();

        return response()->jsonSuccess($this->model->module, $this->actionDelete);
    }

    /**
     * Create error notes for program type.
     *
     * @param  int  $programTypeId
     * @return null
     */
    public function programTypeErrorNotes($programTypeId)
    {
        $programType = $this->model->whereId($programTypeId)->withTrashed()->firstOrFail();
        //Check setup is started or not.
        if (in_array($programType->setup_notes['message'], ['Setup not started', 'Setup incomplete'])) {
            return $this->setError($programType, 'Setup not started or incomplete');
        }

        return $programType->checkForSetupErrors();
    }

    /**
     * Return a success/no-change response for front end status messages.
     *
     * @return Response
     */
    public function programTypeSuccessResponse()
    {
        $module = ($this->model->module === request('module')) ? $this->model->module : ((request('module') === 'sector') ? 'sector' : 'partner');

        return $this->checkResponse($module, request('type'), request('action'));
    }

    /**
     * Update questions with allocation column.
     *
     * @param  array $programTypes
     * @return array
     */
    public function updateQuestionsWithAllocations($programTypes)
    {
        foreach ($programTypes as $programType) {
            $questionWithAllocation = [];
            $trueAllocation = collect($programType->questions_with_allocations)->filter(function ($allocation) {
                return  $allocation['withAllocation'];
            });

            foreach ($programType->ms_questions_group_synced as $question) {
                $checkExist = $trueAllocation->contains('uuid', $question['uuid']);
                $questionWithAllocation[] = [
                    'uuid'           => $question['uuid'],
                    'question'       => $question['question'],
                    'withAllocation' => $checkExist,
                ];
            }

            try {
                $programType->update([
                    'questions_with_allocations'  => $questionWithAllocation,
                ]);
            } catch (QueryException $e) {
                DB::rollBack();

                return [
                    'status'   => $this->statusError,
                ];
            }
        }
    }

    /**
     * @param  ProgramType $programType
     * @param  string      $error
     * @return array
     */
    protected function setError($programType, $error)
    {
        $programType->update([
            'error_notes' => ['Errors present'],
        ]);

        return [
            'error'   => true,
            'message' => $error,
        ];
    }

    /**
     * Calculate sector business days.
     *
     * @param  collection $sectors
     * @return collection
     */
    protected function calculateBusinessDays($sectors)
    {
        $sectors->each(function ($sector) {
            return  $sector->business_days = $this->calculateDays($sector->sector_date_range_start, $sector->sector_date_range_end);
        });
    }

    /**
     * Create Setup-notes for Program-type.
     *
     * @param  [integer] $id
     * @return void
     */
    protected function setupNotes($id)
    {
        /* NOTE: Number of Checks For total Percentage
        (in future if we want to increase any checks then increase this $numberOfChecks accordingly) */
        $numberOfChecks = 4;
        $percentagePerCheck = 100 / $numberOfChecks;

        $programType = $this->model->whereId($id)->withTrashed()->with(['partners', 'sectors', 'questionsAllocations'])->firstOrFail();

        // FIRST_CHECK: For program_types
        $percentage = (!is_null($programType->program_type_date_range_start) && !is_null($programType->program_type_date_range_end)
            && !is_null($programType->program_type_allocation) && !is_null($programType->pacing)) ? $percentagePerCheck : 0;

        // SECOND_CHECK: For partners (if program-type is 'out-tasked')
        if ($programType->code_type === 'in_house') {
            $percentage += $percentagePerCheck;
        } elseif ($programType->code_type === 'out_tasked') {
            $percentage += ($programType->partners->isNotEmpty()) ? $percentagePerCheck : 0;
        }

        // THIRD_CHECK: For sectors
        $percentage += ($programType->sectors->isNotEmpty()) ? $percentagePerCheck : 0;

        // FOURTH_CHECK: For questions_allocations
        if ($programType->questionsAllocations->isNotEmpty() && $programType->selected_allocations) {
            foreach ($programType->ms_questions_group_synced as $question) {
                if (in_array($question['question'], array_pluck($programType->selected_allocations, 'question'))) {
                    $questionValues = array_where($question['values'], function ($value) {
                        return $value['opt_allowed'];
                    });

                    if (empty($questionValues)) {
                        $isChecked = false;

                        break;
                    } else {
                        $isChecked = true;
                    }
                }
            }
            $percentage += ($isChecked) ? $percentagePerCheck : 0;
        }

        $message = ($percentage == 0) ? 'Setup not started' :
                   (($percentage > 0 && $percentage < 100) ? 'Setup incomplete' : 'Setup complete');

        $programType->update(['setup_notes' => [
            'message'             => $message,
            'percentage_complete' => $percentage,
        ]]);
    }
}
