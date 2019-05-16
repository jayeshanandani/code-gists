<?php

namespace App\Traits;

use App\Contacts\Contact;
use App\Companies\Company;
use App\Contacts\ContactAddress;
use App\Companies\CompanyAddress;

trait UpdateEsIndex
{
    /**
     * Create Record On ES Index.
     *
     * @param  [type] $givenSource
     * @return void
     */
    public function createEsResource($givenSource)
    {
        if ($givenSource instanceof Company) {
            app()->make('EsCompany')->store($givenSource);
        } elseif ($givenSource instanceof CompanyAddress) {
            app()->make('EsCompanyAddress')->store($givenSource);
        } elseif ($givenSource instanceof Contact) {
            app()->make('EsContact')->store($givenSource);
        } elseif ($givenSource instanceof ContactAddress) {
            app()->make('EsContactAddress')->store($givenSource);
        } else {
            //Throw Exceptions
        }
    }

    /**
     * Update Record On ES Index.
     *
     * @param  [type] $requestSource
     * @param  [type] $givenSource
     * @return void
     */
    public function updateEsResource($requestSource, $givenSource)
    {
        if ($requestSource instanceof Company) {
            app()->make('EsCompany')->update($requestSource, $givenSource);
        } elseif ($requestSource instanceof CompanyAddress) {
            app()->make('EsCompanyAddress')->update($requestSource, $givenSource);
        } elseif ($requestSource instanceof Contact) {
            app()->make('EsContact')->update($requestSource, $givenSource);
        } elseif ($requestSource instanceof ContactAddress) {
            app()->make('EsContactAddress')->update($requestSource, $givenSource);
        } else {
            //Throw Exceptions
        }
    }
}
