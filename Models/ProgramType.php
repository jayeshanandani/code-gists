<?php

namespace App\ProgramTypes;

use App\BaseModel;
use Carbon\Carbon;

use App\Groups\Group;
use App\Sectors\Sector;
use App\Partners\Partner;
use App\Traits\CascadeSoftDeletes;
use Illuminate\Support\Collection;
use App\QuestionsAllocations\QuestionAllocation;

class ProgramType extends BaseModel
{
    use CascadeSoftDeletes;

    public $module = 'program type';

    public $defaultError = 'Errors present';

    protected $appends = ['code_type', 'code_name', 'selected_allocations'];

    protected $cascadeDeletes = ['partners', 'sectors', 'questionsAllocations'];

    protected $fillable = [
            'uuid',
            'group_id',
            'code',
            'ms_questions_group_uuid',
            'ms_questions_group_synced',
            'program_type_date_range_start',
            'program_type_date_range_end',
            'program_type_allocation',
            'in_house_cpl',
            'in_house_date_shift_days',
            'in_house_delivery_day',
            'pacing',
            'pacing_through_days',
            'questions_with_allocations',
            'setup_notes',
            'error_notes',
            'status',
    ];

    protected $casts = [
        'ms_questions_group_synced'  => 'array',
        'questions_with_allocations' => 'array',
        'setup_notes'                => 'array',
        'error_notes'                => 'array',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    public function sectors()
    {
        return $this->hasMany(Sector::class);
    }

    public function questionsAllocations()
    {
        return $this->hasMany(QuestionAllocation::class);
    }

    public function getCodeTypeAttribute()
    {
        return config('ms_global.program_types.' . $this->code . '.type');
    }

    public function getCodeNameAttribute()
    {
        return config('ms_global.program_types.' . $this->code . '.name');
    }

    public function getSelectedAllocationsAttribute()
    {
        if (!empty($this->questions_with_allocations)) {
            return array_where($this->questions_with_allocations, function ($value) {
                return $value['withAllocation'] === true;
            });
        }
    }

    public function checkForSetupErrors()
    {
        // Reset 'error_notes' before performing checks
        $this->error_notes = [];

        $this->checkPartnerAllocation()
             ->checkSectorAllocations()
             ->checkDates()
             ->checkSectorDateGapsAndOverlaps()
             ->checkQuestionAllocations();

        $this->update(['error_notes' => $this->error_notes]);

        return $this->error_notes;
    }

    /**
     * Check partner allocation and program type allocation.
     */
    public function checkPartnerAllocation()
    {
        if ($this->code_type === 'out_tasked' && $this->partners->sum('partner_allocation') != $this->program_type_allocation) {
            // This can be changed to append a custom error message to the 'error_notes' array.
            $this->error_notes = [$this->defaultError];
            \Log::info('Partner allocation is not equal to program type allocation');
        }

        return $this;
    }

    /**
     * Check sector allocation and program type allocation.
     */
    public function checkSectorAllocations()
    {
        if ($this->sectors->sum('sector_allocation') != $this->program_type_allocation) {
            // This can be changed to append a custom error message to the 'error_notes' array.
            $this->error_notes = [$this->defaultError];
            \Log::info('Sector allocation is not equal to program type allocation');
        }

        return $this;
    }

    /**
     * Check sector start date and program type start date.
     */
    public function checkDates()
    {
        if ($this->code_type === 'in_house') {
            $this->checkSectorDateEndpoints();
        } else {
            foreach ($this->partners as $partner) {
                $this->checkSectorDateEndpoints($partner);
            }
        }

        return $this;
    }

    public function checkSectorDateEndpoints($partner = null)
    {
        $sectors = $this->sectors->where('partner_id', ($partner->id ?? null));

        // Check for a Sector start date equal to Program Type start date
        if (!$this->startDateMatches($sectors)) {
            // This can be changed to append a custom error message to the 'error_notes' array.
            $this->error_notes = [$this->defaultError];
            \Log::info('Sector does not contain program type start date');
        }

        // Check for a Sector end date equal to ProgramType end date minus associated shift days.
        if (!$this->endDateMatches($sectors, $partner)) {
            // This can be changed to append a custom error message to the 'error_notes' array.
            $this->error_notes = [$this->defaultError];
            \Log::info('Sector does not contain In House program type end date');
        }
    }

    public function startDateMatches($sectors)
    {
        return $sectors->contains('sector_date_range_start', $this->program_type_date_range_start);
    }

    public function endDateMatches($sectors, $partner = null)
    {
        $shiftDays = $partner->partner_date_shift_days ?? $this->in_house_date_shift_days;

        $endDate = Carbon::parse($this->program_type_date_range_end)->subDays($shiftDays)->format('Y-m-d');

        return $sectors->contains('sector_date_range_end', $endDate);
    }

    /**
     * Check sector overlaps and gaps between start date and end date.
     */
    public function checkSectorDateGapsAndOverlaps()
    {
        if ($this->code_type === 'out_tasked') {
            $partnerSectorsCollection = $this->sectors->where('partner_id', '<>', null)->groupBy('partner_id');
            // Check Sectors for all partners
            foreach ($partnerSectorsCollection as $partnerSectors) {
                $this->compareGapsAndOverlaps($partnerSectors);
            }
        } else {
            // check Sectors for in-house
            $this->compareGapsAndOverlaps($this->sectors);
        }

        return $this;
    }

    /**
     * Check minimum and maximum value of question allocation with program type and partner allocation.
     */
    public function checkQuestionAllocations()
    {
        // Check if question allocation min values are not less than zero.
        $validMinValues = $this->questionsAllocations->every(function ($questionAllocation) {
            return $questionAllocation->question_allocation_min >= 0;
        });

        if (!$validMinValues) {
            // This can be changed to append a custom error message to the 'error_notes' array.
            $this->error_notes = [$this->defaultError];
            \Log::info('Question allocation min value is below zero');
        }

        if ($this->code_type === 'out_tasked') {
            $this->checkOutTaskedQuestionAllocations();
        } else {
            $this->checkInHouseQuestionAllocations();
        }

        return $this;
    }

    /**
     * Compare sector date endpoints for gaps and overlaps.
     *
     * @param  Collection  $sectors
     * @return ProgramType
     */
    protected function compareGapsAndOverlaps(Collection $sectors)
    {
        $sortedSectors = $sectors->sortBy('sector_date_range_start');

        $startDates = $sortedSectors->pluck('sector_date_range_start');
        $endDates = $sortedSectors->pluck('sector_date_range_end');

        // Remove the first "start date" and the last "end date"
        $startDates->shift();
        $endDates->pop();

        // Create Collection where keys are "end dates" (from PREVIOUS sector), and values are "start dates"
        $dateEndpoints = $endDates->combine($startDates);

        $gapsOrOverlaps = $dateEndpoints->mapToGroups(function ($sectorStartDate, $previousSectorEndDate) {
            $calculatedStartDate = Carbon::parse($previousSectorEndDate)->addDay()->format('Y-m-d');

            // $flag will be -1 if "<" (gap), 1 if ">" (overlap), 0 if "==" (no gap/overlap)
            $flag = $calculatedStartDate <=> $sectorStartDate;

            return $flag > 0 ? ['overlaps' => $flag]
                             : ['gaps' => -$flag];
        });

        if (collect($gapsOrOverlaps->get('gaps'))->sum() > 0) {
            // This can be changed to append a custom error message to the 'error_notes' array.
            $this->error_notes = [$this->defaultError];
            \Log::info('There are gaps in the sector date ranges');
        }

        if (collect($gapsOrOverlaps->get('overlaps'))->sum() > 0) {
            // This can be changed to append a custom error message to the 'error_notes' array.
            $this->error_notes = [$this->defaultError];
            \Log::info('There are overlaps in the sector date ranges');
        }

        return $this;
    }

    /**
     * Check minimum and maximum value of question allocation with partner allocation.
     */
    protected function checkOutTaskedQuestionAllocations()
    {
        foreach ($this->partners as $partner) {
            $questionsAllocations = $this->questionsAllocations()->where('partner_id', $partner->id)->get();

            $this->compareQuestionAllocations($questionsAllocations, $partner->partner_allocation);
        }
    }

    /**
     * Check minimum and maximum value of question allocation max with program type allocation.
     */
    protected function checkInHouseQuestionAllocations()
    {
        $this->compareQuestionAllocations($this->questionsAllocations, $this->program_type_allocation);
    }

    /**
     * Check for allowable allocation max/min values given the total allocation.
     *
     * @param Collection $questionsAllocations
     * @param int        $totalAllocation
     */
    protected function compareQuestionAllocations(Collection $questionsAllocations, $totalAllocation)
    {
        $logStr = $this->code_type === 'in_house' ? 'Program Type' : 'Partner';

        $minAllocation = $questionsAllocations->sum('question_allocation_min');

        if ($minAllocation > $totalAllocation) {
            // This can be changed to append a custom error message to the 'error_notes' array.
            $this->error_notes = [$this->defaultError];
            \Log::info("Minimum question allocation exceeds the $logStr's allocation");
        }

        $maxExceedsAllocation = $questionsAllocations->every(function ($questionAllocation) use ($totalAllocation) {
            return $questionAllocation->question_allocation_max > $totalAllocation;
        });

        if ($maxExceedsAllocation) {
            // This can be changed to append a custom error message to the 'error_notes' array.
            $this->error_notes = [$this->defaultError];
            \Log::info("Question allocation max value exceeds the $logStr's allocation");
        }
    }
}
