<?php

namespace App\Http\Controllers\Setups;

use App\Groups\Group;
use App\Services\Guzzle;
use App\Partners\Partner;
use Illuminate\Http\Response;
use App\ProgramTypes\ProgramType;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Http\Controllers\BaseController;
use App\QuestionsAllocations\QuestionAllocation;

class SetupsController extends BaseController
{
    protected $group;

    protected $programType;

    protected $questionAllocation;

    protected $guzzle;

    /**
     * __construct.
     *
     * @param Group              $group
     * @param ProgramType        $programType
     * @param QuestionAllocation $questionAllocation
     * @param Guzzle             $guzzle
     */
    public function __construct(Group $group, ProgramType $programType, QuestionAllocation $questionAllocation, Guzzle $guzzle)
    {
        parent::__construct();
        $this->group = $group;
        $this->programType = $programType;
        $this->questionAllocation = $questionAllocation;
        $this->guzzle = $guzzle;
    }

    /**
     * Setup group for selected program types.
     *
     * @return view
     */
    public function groupSetup()
    {
        validateAccess('groups__create');

        return view('groups.setup.setup');
    }

    /**
     * Retrieve group with program types.
     *
     * @param  uuid                      $uuid
     * @return \Illuminate\Http\Response
     */
    public function getGroupAndProgramType($uuid)
    {
        validateAccess('groups__create');

        $isDefault = false;
        $flag = false;
        $code = request()->query('code');

        $group = $this->group->whereUuid($uuid)->with(['programTypes' => function ($query) use ($code) {
            $code ? $query->whereNull('program_type_date_range_start')->orderByRaw('code = ? desc', [$code]) : $query->orderBy('id');
        }])->firstOrFail();

        foreach ($group->programTypes as $key => $value) {
            data_fill($value, 'is_completed', $value->program_type_date_range_start ? true : false);
            data_fill($value, 'step', $key);
            if (!$value['is_completed'] && !$isDefault && !$flag) {
                $isDefault = true;
                $flag = true;
            } else {
                $isDefault = false;
            }

            data_fill($value, 'is_active', $isDefault);
        }

        return response()->json($group);
    }

    /**
     * Retrieve lists of accounts with partner.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAccountsPartner()
    {
        $url = config('ms_global.ms_urls.' . config('app.env') . '.accounts') . config('ms_specific.account.get_partners');
        $response = $this->guzzle->get($url);

        if (!$response['data']['data']) {
            return $this->prepareResponse('Account record not found.', $this->statusError, Response::HTTP_NOT_FOUND);
        }

        return response()->json($response['data']['data'], $response['statusCode']);
    }

    /**
     * Store setup data in respective table.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $programType = $this->programType->whereUuid(request('uuid'))->firstOrFail();

        DB::beginTransaction();

        try {
            $programType->update([
                'program_type_date_range_start' => parseDate(request('start_date'), 'Y-m-d'),
                'program_type_date_range_end'   => parseDate(request('end_date'), 'Y-m-d'),
                'program_type_allocation'       => request('allocation'),
                'in_house_date_shift_days'      => request('date_shift_days'),
                'in_house_delivery_day'         => request('delivery_day'),
                'in_house_cpl'                  => request('cpl'),
                'pacing'                        => request('pacing'),
                'pacing_through_days'           => request('pacing_through_days'),
                'questions_with_allocations'    => request('questions_with_allocations'),
                'status'                        => $this->statusActive,
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json('Group setup was unsuccessful due to program-type error.', Response::HTTP_NOT_FOUND);
        }

        if (request('selected_partners') && !empty(request('selected_partners'))) {
            $response = $this->createPartners($programType);
            if ($response['error']) {
                return response()->json($response['message'], Response::HTTP_NOT_FOUND);
            }
        } elseif (request('sectors') && !empty(request('sectors'))) {
            $response = $this->createSectors(request('sectors'), $programType);

            if ($response['error']) {
                return response()->json($response['message'], Response::HTTP_NOT_FOUND);
            }

            if ($programType->ms_questions_group_synced) { //Somehow queue doesn't work then this function should not execute.
                $response = $this->createQuestionAllocations($programType);
                if ($response['error']) {
                    return response()->json($response['message'], Response::HTTP_NOT_FOUND);
                }
            }
        }
        DB::commit();

        return response()->json('Group setup successfully.', Response::HTTP_OK);
    }

    /**
     * Store Partner.
     *
     * @param ProgramType $programType
     */
    protected function createPartners($programType)
    {
        foreach (request('selected_partners') as $partner) {
            try {
                $newPartner = $programType->partners()->create(array_merge($partner, [
                    'uuid'   => $this->getUuid(),
                    'status' => $this->statusActive,
                    ]
                ));
            } catch (QueryException $e) {
                DB::rollBack();

                return [
                    'message' => 'Group setup was unsuccessful due to partner error.',
                    'error'   => true,
                ];
            }

            //Create sectors
            if (!empty($partner['sectors'])) {
                $response = $this->createSectors($partner['sectors'], $programType, $newPartner);
                if ($response['error']) {
                    return $response;
                }
            }

            //Create questions allocations
            if ($programType->ms_questions_group_synced) { //Somehow queue doesn't work then this function should not execute.
                $response = $this->createQuestionAllocations($programType, $newPartner);
                if ($response['error']) {
                    return response()->json($response['message'], Response::HTTP_NOT_FOUND);
                }
            }
        }
    }

    /**
     * Store Sectors.
     *
     * @param array        $sectors
     * @param ProgramType  $programType
     * @param Partner|null $partner
     */
    protected function createSectors($sectors, $programType, $partner = null)
    {
        foreach ($sectors as $sector) {
            try {
                $programType->sectors()->create([
                    'uuid'                    => $this->getUuid(),
                    'partner_id'              => $partner->id ?? null,
                    'sector_date_range_start' => $sector['start_date'],
                    'sector_date_range_end'   => $sector['end_date'],
                    'sector_allocation'       => $sector['allocation'],
                    'status'                  => $this->statusActive,
                ]);
            } catch (QueryException $e) {
                DB::rollBack();

                return [
                    'message'       => 'Group setup was unsuccessful due to sectors error.',
                    'error'         => true,
                ];
            }
        }
    }

    /**
     * Store QuestionAllocations.
     *
     * @param ProgramType  $programType
     * @param Partner|null $partner
     */
    protected function createQuestionAllocations($programType, $partner = null)
    {
        foreach ($programType->ms_questions_group_synced as $question) {
            if (in_array($question['question'], array_pluck($programType->selected_allocations, 'question'))) {
                $questionValues = array_where($question['values'], function ($value) {
                    return $value['opt_allowed'];
                });

                foreach ($questionValues as $value) {
                    try {
                        $programType->questionsAllocations()->create([
                            'uuid'                    => $this->getUuid(),
                            'partner_id'              => $partner->id ?? null,
                            'question'                => $question['question'],
                            'value'                   => $value['value'],
                            'question_allocation_min' => 0,
                            'question_allocation_max' => $partner->partner_allocation ?? $programType->program_type_allocation,
                            'status'                  => $this->statusActive,
                        ]);
                    } catch (QueryException $e) {
                        DB::rollBack();

                        return [
                            'message'  => 'Group setup was unsuccessful due to question-allocations error.',
                            'error'    => true,
                        ];
                    }
                }
            }
        }
    }
}
