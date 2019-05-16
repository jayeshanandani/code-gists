<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Sectors\Sector;
use Tests\TestingDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SectorTest extends TestCase
{
    use TestingDataSeeder, RefreshDatabase;

    public function setup()
    {
        parent::setUp();
        $this->makeTestingData();
        $this->invalidUuid = $this->getUuid();
        $this->date = Carbon::now();
        $this->setSession();
    }

    /**
     * A test should create new Sector Successfully.
     */
    public function test_should_create_new_sector()
    {
        $response = $this->post('sectors', $this->sectorData);
        $response->assertSuccessful()->assertExactJson([
            'success' => 'The sector was created successfully.',
            'uuid'    => Sector::orderBy('id', 'desc')->first()->uuid,
        ]);
        $this->assertDatabaseHas('sectors', [
            'program_type_id'                => $this->sectorData['program_type_id'],
            'partner_id'                     => $this->sectorData['partner_id'],
            'sector_date_range_start'        => $this->sectorData['sector_date_range_start'],
            'sector_date_range_end'          => $this->sectorData['sector_date_range_end'],
            'sector_allocation'              => $this->sectorData['sector_allocation'],
        ]);
    }

    /**
     * A test should update Sector Successfully.
     */
    public function test_should_update_sector()
    {
        $response = $this->put('sectors/' . $this->sector->uuid, $this->sectorData);
        $response->assertSuccessful()->assertExactJson([
            'success' => 'The sector was updated successfully.',
        ]);
        $this->assertDatabaseHas('sectors', [
            'uuid'                           => $this->sector->uuid,
            'sector_date_range_start'        => $this->sectorData['sector_date_range_start'],
            'sector_date_range_end'          => $this->sectorData['sector_date_range_end'],
            'sector_allocation'              => $this->sectorData['sector_allocation'],
        ]);
    }

    /**
     * A test should give warning on update sector with same data.
     */
    public function test_should_give_nothing_changed_on_update_sector()
    {
        $response = $this->put('sectors/' . $this->sector->uuid, $this->sector->toArray());
        $response->assertSuccessful()->assertExactJson([
            'info' => 'Nothing changed in sector.',
        ]);
        $this->assertDatabaseHas('sectors', [
            'uuid'                           => $this->sector->uuid,
            'sector_date_range_start'        => $this->sector->sector_date_range_start,
            'sector_date_range_end'          => $this->sector->sector_date_range_end,
            'sector_allocation'              => $this->sector->sector_allocation,
        ]);
    }

    /**
     * A test should give an error for invalid uuid on update sector.
     */
    public function test_should_give_an_error_on_invalid_uuid_while_update()
    {
        $response = $this->ajaxPut('/sectors/' . $this->invalidUuid, $this->sector->toArray());
        $response->assertNotFound()->assertExactJson([
            'error' => 'Record not found.',
        ]);
        $this->assertDatabaseMissing('sectors', ['uuid' => $this->invalidUuid]);
    }

    /**
     * A test should destroy Sector Successfully.
     */
    public function test_should_destroy_sector()
    {
        $response = $this->delete('/sectors/' . $this->sector->uuid);
        $response->assertSuccessful()->assertExactJson([
            'success' => 'The sector was deleted successfully.',
        ]);
        $this->assertSoftDeleted('sectors', ['uuid' => $this->sector->uuid]);
    }

    /**
     * A test should give an error due to invalid uuid while delete sector.
     */
    public function test_should_give_error_for_invalid_uuid_on_delete_sector()
    {
        $response = $this->ajaxDelete('/sectors/' . $this->invalidUuid);
        $response->assertNotFound()->assertExactJson([
            'error' => 'Record not found.',
        ]);
        $this->assertDatabaseMissing('sectors', ['uuid' => $this->invalidUuid]);
    }

    /**
     * A test should calculate number of business days based on date.
     */
    public function test_should_calculate_business_days()
    {
        $startDate = clone $this->date;
        $endDate = $this->date->addMonth()->format('Y-m-d');

        $noOfBusinessDays = $this->calculateBusinessDays();

        $response = $this->get('calculate/business-days/?start_date=' . $startDate->format('Y-m-d') . '&end_date=' . $endDate);
        $response->assertSuccessful();
        $this->assertEquals($noOfBusinessDays, $response->getContent());
    }

    /**
     * A test should calculate allocation for sector.
     */
    public function test_should_calculate_allocation_based_on_sector_date()
    {
        $request = $this->prepareAllocationRequestData();

        $response = $this->get('calculate/allocations/?overall_allocation=' . $request['requestData']['overall_allocation'] .
                                                            '&overall_start_date=' . $request['requestData']['overall_start_date'] .
                                                            '&overall_end_date=' . $request['requestData']['overall_end_date'] .
                                                            '&sector_start_date=' . $request['requestData']['sector_start_date'] .
                                                            '&sector_end_date=' . $request['requestData']['sector_end_date']);

        $response->assertSuccessful();
        $this->assertEquals($request['allocation'], $response->getContent());
    }

    /**
     * A test should calculate allocation for sector based on previous sector data.
     */
    public function test_should_calculate_allocation_based_on_previous_sector_date()
    {
        $request = $this->prepareAllocationRequestData();

        $sectorStartDate = carbon::parse($request['requestData']['sector_end_date'])->addDay();
        $sectorEndDate = (clone $sectorStartDate)->addDay(4);

        $sectorBusinessDays = $this->calculateBusinessDays($sectorStartDate, $sectorEndDate);

        $businessDays = $sectorBusinessDays + $request['businessDay'];

        $allocations = round(($request['allocationPerDay'] * $businessDays) - $request['allocation']);

        $response = $this->get('calculate/allocations/?overall_allocation=' . $request['requestData']['overall_allocation'] .
                                                            '&overall_start_date=' . $request['requestData']['overall_start_date'] .
                                                            '&overall_end_date=' . $request['requestData']['overall_end_date'] .
                                                            '&sector_start_date=' . $sectorStartDate->format('Y-m-d') .
                                                            '&sector_end_date=' . $sectorEndDate->format('Y-m-d') .
                                                            '&round_end_date=' . $request['requestData']['sector_end_date']);

        $response->assertSuccessful();
        $this->assertEquals($allocations, $response->getContent());
    }

    /**
     * A test should calculate sector based on weekly selector.
     */
    public function test_should_calculate_sector_based_on_weekly_selector()
    {
        $request = $this->prepareSectorRequestData('weekly');

        $response = $this->post('calculateSectors', $request)->getOriginalContent();

        foreach ($response as $sector) {
            //This assertion check the every sector last date day must be the delivery day
            //If its match then assert true else false
            if (carbon::parse($sector['end_date'])->format('l') === $request['delivery_day']) {
                $this->assertTrue(true);
            } else {
                $this->assertFalse(false);
            }
        }
        //This assertion check the last sector end date must be the delivery end date
        $this->assertEquals(last($response)['end_date'], $request['overall_end_date']);
    }

    /**
     * A test should calculate sector based on monthly selector.
     */
    public function test_should_calculate_sector_based_on_monthly_selector()
    {
        $request = $this->prepareSectorRequestData('monthly');

        $response = $this->post('calculateSectors', $request)->getOriginalContent();

        foreach ($response as $sector) {
            //This assertion check the every sector last date day must be the delivery day
            //If its match then assert true else false
            if ($sector['end_date'] === carbon::parse($request['overall_end_date'])->endOfMonth()->format('Y-m-d')) {
                $this->assertTrue(true);
            } else {
                $this->assertFalse(false);
            }
        }
        //This assertion check the last sector end date must be the delivery end date
        $this->assertEquals(last($response)['end_date'], $request['overall_end_date']);
    }

    /**
     * A test should give error when diff of programType date range
     * is less then date shift when calculating sector.
     */
    public function test_should_give_error_when_diff_of_sectors_date_less_then_date_shift_when_calculating_sector()
    {
        $request = $this->prepareSectorRequestData('monthly');
        $request['date_shift'] = 50;

        $response = $this->post('calculateSectors', $request)->getOriginalContent();
        $this->assertEquals($response, 'Please select appropriate shift days.');
    }

    /**
     * Prepare request data for calculate sector allocation.
     */
    protected function prepareAllocationRequestData()
    {
        $overallAllocation = 400;
        $overallStartDate = $this->date;
        $overallEndDate = (clone $this->date)->addMonth();
        $overallBusinessDays = $this->calculateBusinessDays($overallStartDate, $overallEndDate);

        $allocationPerDay = round(($overallAllocation / $overallBusinessDays), 3);

        $sectorStartDate = Carbon::now()->addDay(2);
        $sectorEndDate = Carbon::now()->addDay(5);

        $sectorBusinessDays = $this->calculateBusinessDays($sectorStartDate, $sectorEndDate);
        $allocation = round($sectorBusinessDays * $allocationPerDay);

        $data = [
            'overall_allocation'  => $overallAllocation,
            'overall_start_date'  => $overallStartDate->format('Y-m-d'),
            'overall_end_date'    => $overallEndDate->format('Y-m-d'),
            'sector_start_date'   => $sectorStartDate->format('Y-m-d'),
            'sector_end_date'     => $sectorEndDate->format('Y-m-d'),
        ];

        return ['allocation' => $allocation, 'businessDay' => $sectorBusinessDays, 'allocationPerDay' => $allocationPerDay, 'requestData' => $data];
    }

    /**
     * Prepare request data for calculate sector based on weekly or monthly selector.
     * @param mixed $selector
     */
    protected function prepareSectorRequestData($selector)
    {
        $overallStartDate = $this->date;
        $overallEndDate = (clone $this->date)->addMonth();
        $programEndDate = (clone $overallEndDate)->addDays(3);

        return [
            'overall_allocation' => 400,
            'overall_start_date' => $overallStartDate->format('Y-m-d'),
            'overall_end_date'   => $overallEndDate->format('Y-m-d'),
            'program_end_date'   => $programEndDate->format('Y-m-d'),
            'date_shift'         => 3,
            'auto_fill_selector' => $selector,
            'delivery_day'       => 'Friday',
        ];
    }
}
