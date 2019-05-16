<?php

namespace App\Services;

class CsvParser
{
    public function getRowCountCsv($file)
    {
        $rows = explode(PHP_EOL, $file);
        $data = [];
        foreach ($rows as $row) {
            if ($row) {
                $data[] = str_getcsv($row);
            }
        }

        return count($data);
    }

    public function parseCsv($file, $numberOfRecords = null)
    {
        $result = [];

        if (isset($numberOfRecords)) {
            $firstXRows = explode(PHP_EOL, $file, $numberOfRecords + 1);
            $rows = array_slice($firstXRows, 0, $numberOfRecords);
        } else {
            $rows = explode(PHP_EOL, $file);
        }
        $data = [];

        foreach ($rows as $row) {
            if ($row) {
                $data[] = str_getcsv($row);
            }
        }

        $result['data'] = $data;
        $result['totalRecord'] = count($data);
        $result['totalColumn'] = count($data[0]);

        return $result;
    }

    public function downloadCSV($fileName, $headerNames, $data)
    {
        $handle = fopen($fileName, 'w+');
        fputcsv($handle, $headerNames);

        foreach ($data as $key => $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        $headers = [
            'Content-Type' => 'text/csv',
        ];

        return response()->download($fileName, null, $headers);
    }
}
