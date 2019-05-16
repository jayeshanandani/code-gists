<?php

namespace App\Exports;

use App\Schools\School;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\JsonResponse;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExportFileFormat extends ExportData implements Responsable, WithEvents, ShouldAutoSize
{
    use Exportable, RegistersEventListeners;

    protected $table;

    protected $fileName;

    public function __construct($table)
    {
        $this->table = $table;
        $this->fileName = strtoupper(request()->module) . '_report.xlsx';
    }

    /**
     * @return array
     */
    public function registerEvents() : array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $data = $this->dataCollection($this->table);    // table's data collection

                $data = $data->toArray();

                if (empty($data)) {
                    return response()->json(['error' => 'Empty Data.']);
                }

                $headers = $this->array_keys_multi($data); // table's headings

                $data = json_decode(json_encode($data), true);

                if ($data instanceof JsonResponse) {
                    return $data;
                }

                $sheet = $event->writer->getActiveSheet();

                $sheet->getDefaultColumnDimension()->setWidth(22);
                $filePath = public_path('images/default.png');
                if (request()->school_id && !empty(request()->school_id) && !is_array(json_decode(request()->school_id, true))) {
                    $school = School::with('address')->where('id', request()->school_id)->first();
                    if ($school) {
                        $school->logo = optional($school->getMedia('school_logo'))->first();
                        if (!is_null(($school->logo))) {
                            $school->logo_file_name = $school->logo ? ($school->logo->filename . '.' . $school->logo->extension) : '';
                            $filePath = storage_path('app/public/school-logo/' . $school->logo_file_name);
                        }
                    } else {
                        return response()->json(['error' => 'school not found']);
                    }

                    $maxWidth = 280;
                    $maxHeight = 95;
                    // MERGE CELLS
                    $sheet->mergeCells('A1:B5');
                    $sheet->mergeCells('C2:F2');
                    $sheet->mergeCells('C3:F3');
                    $sheet->mergeCells('C4:F4');
                    $sheet->mergeCells('C5:F5');

                    $sheet->getStyle('C2:D5')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);

                    $sheet->getStyle('A8:Z8')->applyFromArray(['font' => [
                        'bold' => true,
                    ]]);

                    $sheet->getStyle('A8:Z8')->getAlignment()->setWrapText(true);

                    //set logo to sheet
                    $drawing = new Drawing();
                    $drawing->setName('PhpSpreadsheet logo');
                    $drawing->setPath($filePath);
                    $drawing->setCoordinates('A1');
                    $drawing->setHeight($maxHeight);
                    $drawing->setWorksheet($sheet);

                    // Set Header values
                    $sheet->setCellValue('C2', $school->name);
                    $sheet->setCellValue('C3', $school->address->address_1);
                    $sheet->setCellValue('C4', $this->fileName);
                    $current_date = new \DateTime();
                    $sheet->setCellValue('C5', 'DATE : ' . $current_date->format('d-m-Y H:i:s'));
                    $sheet->fromArray($headers, null, 'A8');
                    $sheet->fromArray($data, null, 'A9');
                } else {
                    $sheet->getStyle('A1:Z1')->applyFromArray(['font' => [
                        'bold' => true,
                        ],
                    ]);
                    $sheet->getStyle('A1:Z1000')->applyFromArray([
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        ],
                    ]);
                    $sheet->getStyle('A1:Z1')->getAlignment()->setWrapText(true);
                    $sheet->fromArray($headers, null, 'A1');
                    $sheet->fromArray($data, null, 'A2');
                }
            },
        ];
    }
}
