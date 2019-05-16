<?php

namespace App\Exports;

class ExportData
{
    protected $obj;

    /**
     * returns table's data collection.
     * @param mixed $module
     */
    public function dataCollection($module)
    {
        /*
         *  @return array
         * returns table's data
         */
        if ($module && !empty($module)) {
            $this->obj = new \App\Exports\ExportQueryAndDataMaster();
        } else {
            return response()->json('please select module.');
        }

        return $this->obj->export();
    }

    /**
     *  @return array
     * returns table's headings
     */
    public function array_keys_multi(array $array)
    {
        foreach ($array as $key => $value) {
            return array_keys((array) $value);
        }
    }
}
