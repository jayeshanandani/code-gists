<?php

namespace App\Http\Controllers\Sectors;

use Carbon\Carbon;
use App\Sectors\Sector;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\CalculateSectorData;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Sector\SectorRequest;

class SectorsController extends BaseController
{
    use CalculateSectorData;

    protected $model;

    /**
     * __construct.
     *
     * @param sector $sector
     */
    public function __construct(Sector $sector)
    {
        parent::__construct();
        $this->model = $sector;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  SectorRequest             $request
     * @return \Illuminate\Http\Response
     */
    public function store(SectorRequest $request)
    {
        validateAccess('sectors__create');
        request()->request->add(['uuid' => $this->getUuid(), 'status' => $this->statusActive]);

        $sector = $this->model->create(request()->all());

        return response()->jsonSuccess($this->model->module, $this->actionSave, ['uuid' => $sector->uuid]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  SectorRequest             $request
     * @param  uuid                      $uuid
     * @return \Illuminate\Http\Response
     */
    public function update(SectorRequest $request, $uuid)
    {
        validateAccess('sectors__update');
        $request->offsetSet('sector_date_range_start', parseDate($request->sector_date_range_start, 'Y-m-d'));
        $request->offsetSet('sector_date_range_end', parseDate($request->sector_date_range_end, 'Y-m-d'));

        $sector = $this->model->whereUuid($uuid)->firstOrFail();

        $sector->fill($request->only(['sector_date_range_start', 'sector_date_range_end', 'sector_allocation']));
        $sector->save();

        if (empty($sector->getChanges())) {
            return response()->jsonInfo($this->model->module, $this->notChanged);
        }

        return response()->jsonSuccess($this->model->module, $this->actionUpdate);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  uuid                      $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        validateAccess('sectors__delete');
        $sector = $this->model->whereUuid($uuid)->firstOrFail();

        $sector->delete();

        return response()->jsonSuccess($this->model->module, $this->actionDelete);
    }

    /**
     * Calculate sector allocation based on incoming parameters.
     *
     * @return int
     */
    public function calculateSectorAllocations()
    {
        return  $this->calculateAllocation(request('overall_allocation'), request('overall_start_date'),
                    request('overall_end_date'), request('sector_start_date'), request('sector_end_date'),
                    request('round_end_date'));
    }

    /**
     * Calculate Sectors based on weekly and monthly.
     */
    public function calculateSectors()
    {
        // Delivery Dates
        $startDate = Carbon::parse(request('overall_start_date'));
        $endDate = Carbon::parse(request('overall_end_date'));

        // Diff days between Program END_DATE and Delivery START_DATE
        $diff = Carbon::parse(request('program_end_date'))->diffInDays($startDate);

        if (request('date_shift') > $diff) {
            return response()->json('Please select appropriate shift days.', Response::HTTP_BAD_REQUEST);
        }

        $deliveryDates = [];

        if (request('auto_fill_selector') === 'monthly') {
            $deliveryDates = $this->getDeliveryDates($startDate, $endDate, 'monthly');
        } elseif (request('auto_fill_selector') === 'weekly') {
            $deliveryDates = $this->getDeliveryDates($startDate, $endDate, 'weekly');
        }

        $startDates = $this->prepareSectorStartDates($startDate, $endDate, $deliveryDates);
        $endDates = $this->prepareSectorEndDates($endDate, $deliveryDates);

        $sectorsDateCollection = collect($startDates)->zip($endDates);

        foreach ($sectorsDateCollection as $key => $dateChunk) {
            $roundEndDate = $key ? $sectorsDateCollection[$key - 1]->last() : null;

            $sectorStartDate = $dateChunk->first();
            $sectorEndDate = $dateChunk->last();

            $allocation = $this->calculateAllocation(request('overall_allocation'), $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'), $sectorStartDate, $sectorEndDate, $roundEndDate);

            $sectors[] = [
                'start_date'    => $sectorStartDate,
                'end_date'      => $sectorEndDate,
                'business_days' => $this->businessDays,
                'allocation'    => $allocation,
            ];
        }

        return $sectors ?? [];
    }

    /**
     * Calculate no of business days between two given date.
     *
     * @return int
     */
    public function calculateBusinessDays()
    {
        return $this->calculateDays(request('start_date'), request('end_date'));
    }

    /**
     * Get delivery dates of given date range.
     *
     * @param  carbon $startDate
     * @param  carbon $endDate
     * @param  string $selector
     * @return array
     */
    protected function getDeliveryDates($startDate, $endDate, $selector)
    {
        $deliveryDates = [];

        if ($selector === 'monthly') {
            $endDateEndOfMonth = (clone $endDate)->endOfMonth();

            $endOfMonth = (clone $startDate)->endOfMonth();
            $deliveryDates[] = (clone $endOfMonth)->format('Y-m-d');

            while (!$endOfMonth->equalTo($endDateEndOfMonth)) {
                $endOfMonth = $endOfMonth->addDay()->endOfMonth();
                $deliveryDates[] = (clone $endOfMonth)->format('Y-m-d');
            }
        } elseif ($selector === 'weekly') {
            // $endDate->addDay()
            // One day is added as diffInDaysFiltered()
            // ignoring last day from the date range.
            for ($date = (clone $startDate); $date->lte($endDate); $date->addDay()) {
                if (($date->format('l') === request('delivery_day'))) {
                    // Weekly  : Get List of Delivery Dates
                    $deliveryDates[] = $date->format('Y-m-d');
                }
            }
        }

        return $deliveryDates;
    }

    /**
     * Prepare sectors end dates.
     *
     * @param carbon $endDate
     * @param array  $deliveryDates
     *
     * @return array
     */
    protected function prepareSectorEndDates($endDate, $deliveryDates)
    {
        $clonedEndDate = (clone $endDate)->format('Y-m-d');

        if (request('auto_fill_selector') === 'weekly') {
            if (last($deliveryDates) !== $clonedEndDate) {
                $deliveryDates[] = $clonedEndDate;
            }
        } else {
            $deliveryDates[count($deliveryDates) - 1] = $clonedEndDate;
        }

        return $deliveryDates;
    }

    /**
     * Prepare sectors start dates.
     *
     * @param carbon $startDate
     * @param carbon $endDate
     * @param array  $deliveryDates
     *
     * @return array
     */
    protected function prepareSectorStartDates($startDate, $endDate, $deliveryDates)
    {
        $startDates[] = (clone $startDate)->format('Y-m-d');

        foreach ($deliveryDates as $deliveryDate) {
            $newDeliveryDate = Carbon::parse($deliveryDate)->addDay();
            if ($newDeliveryDate->lte($endDate)) {
                $startDates[] = $newDeliveryDate->format('Y-m-d');
            }
        }

        return $startDates;
    }
}
