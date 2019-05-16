<?php

namespace App\Http\Controllers\Leads;

use App\Leads\Lead;
use App\Contacts\Contact;
use App\Companies\Company;
use App\Http\Controllers\BaseController;

class LeadsController extends BaseController
{
    /**
     * Lead Model.
     */
    protected $model;

    /**
     * Company Model.
     */
    protected $company;

    /**
     * Contact Model.
     */
    protected $contact;

    /**
     * __construct.
     *
     * @param Lead    $leadModel
     * @param Company $company
     * @param Contact $contact
     */
    public function __construct(Lead $leadModel, Company $company, Contact $contact)
    {
        parent::__construct();
        $this->model = $leadModel;
        $this->company = $company;
        $this->contact = $contact;
    }

    /**
     * Create Or Update Lead Based On Action.
     *
     * @param  array $data
     * @return array
     */
    public function store(array $data)
    {
        $lead = $this->model->whereUuid($data['lead_uuid'])->first();

        if ($data['action'] === strtolower($this->actionCreate) && $lead) {
            return [
                'status'  => 'error',
                'message' => "Lead UUID already exists: \"{$data['lead_uuid']}\"",
            ];
        } elseif ($data['action'] === strtolower($this->actionUpdate) && empty($lead)) {
            return [
                'status'  => 'error',
                'message' => "Lead UUID does not exist: \"{$data['lead_uuid']}\"",
            ];
        }

        return $this->actionCreateOrUpdate($data, $lead);
    }

    /**
     * Create OR Update Lead.
     *
     * @param  array  $data
     * @param  [type] $lead
     * @return array
     */
    public function actionCreateOrUpdate(array $data, $lead)
    {
        // Prepare data for Company and Contact and Store/Update the same
        $response = $this->createCompanyContact($data);

        if ($data['action'] === strtolower($this->actionCreate)) {
            // Create Lead
            $this->model->createLead($response);
        } elseif ($data['action'] === strtolower($this->actionUpdate)) {
            // Update Lead
            $this->model->updateLead($response, $lead->load('companyAddresses', 'contactAddresses', 'leadCompanyCustomQuestions', 'leadContactCustomQuestions'));
        }

        return $response;
    }

    /**
     * Create OR Update Company and Contact data.
     *
     * @param  array $data
     * @return array
     */
    public function createCompanyContact(array $data): array
    {
        // Create OR Update Company
        $company = $this->company->prepareDataSet($data, 'company')->createOrUpdateCompany();
        $data['contact']['company_id'] = $company->id;
        // Create OR Update Contact
        $contact = $this->contact->prepareDataSet($data, 'contact')->createOrUpdateContact();
        // Prepare Company and Contact data for Lead
        $lead = $this->model->prepareLeadDataSet($company->id, $contact->id, $data);

        return [
            'lead'    => $lead,
            'contact' => $contact,
            'company' => $company,
        ];
    }
}
