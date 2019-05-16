<?php

namespace App\Exports;

use App\Exports\Queries\QueryProvider;

class ExportQueryAndDataMaster implements ExportInterface
{
    /**
     * @var QueryProvider
     */
    protected $queryProvider;

    /**
     * BasePedigree constructor.
     */
    public function __construct()
    {
        $this->queryProvider = new QueryProvider;
    }

    public function export()
    {
        $module = request()->module;

        return $this->queryProvider->queryMaster($module)->get();
    }
}
