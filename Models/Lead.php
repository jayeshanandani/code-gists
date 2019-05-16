<?php

namespace App\Leads;

use App\BaseModel;
use App\Contacts\Contact;
use App\Companies\Company;
use App\Traits\CascadeSoftDeletes;
use App\Traits\CompareSourceAndUpdate;

class Lead extends BaseModel
{
    use CascadeSoftDeletes,CompareSourceAndUpdate;

    public $module = 'lead';

    protected $fillable = [
        'uuid',
        'company_id',
        'contact_id',
        'incoming_company_id',
        'incoming_contact_id',
        'company_name',
        'company_phone',
        'company_industry',
        'company_size',
        'company_domain_name',
        'company_size_pick_list',
        'company_revenue_pick_list',
        'company_industry_pick_list',
        'contact_first_name',
        'contact_last_name',
        'contact_email',
        'contact_phone',
        'contact_job_title',
        'contact_phone_extension',
        'contact_job_title_pick_list',
        'contact_prefix',
        'contact_linkedin_url',
        'source',
    ];

    protected $casts = [
        'company_name'                => 'array',
        'company_phone'               => 'array',
        'company_industry'            => 'array',
        'company_size'                => 'array',
        'contact_first_name'          => 'array',
        'company_domain_name'         => 'array',
        'company_size_pick_list'      => 'array',
        'company_revenue_pick_list'   => 'array',
        'company_industry_pick_list'  => 'array',
        'contact_last_name'           => 'array',
        'contact_email'               => 'array',
        'contact_phone'               => 'array',
        'contact_job_title'           => 'array',
        'contact_phone_extension'     => 'array',
        'contact_job_title_pick_list' => 'array',
        'contact_prefix'              => 'array',
        'contact_linkedin_url'        => 'array',
    ];

    public function histories()
    {
        return $this->hasMany(LeadHistory::class);
    }

    public function companyAddresses()
    {
        return $this->hasMany(LeadCompanyAddress::class);
    }

    public function contactAddresses()
    {
        return $this->hasMany(LeadContactAddress::class);
    }

    public function leadCompanyCustomQuestions()
    {
        return $this->hasMany(LeadCompanyCustomQuestion::class);
    }

    public function leadContactCustomQuestions()
    {
        return $this->hasMany(LeadContactCustomQuestion::class);
    }

    /**
     * Create Lead.
     *
     * @param  array $data
     * @return void
     */
    public function createLead(array $data): void
    {
        $lead = $this->create($data['lead']);
        $lead->histories()->create(array_except($data['lead'], ['uuid']));

        $lead->createRelatedLeadModel($data['company'], $data['contact']);
    }

    /**
     * Create Lead And Related Model.
     *
     * @param  Company $company
     * @param  Contact $contact
     * @return void
     */
    public function createRelatedLeadModel(Company $company, Contact $contact): void
    {
        //Create Company Address
        $leadCompanyAddress = $this->companyAddresses()->create($company->addresses->first()->toArray());
        $leadCompanyAddress->histories()->create($leadCompanyAddress->toArray());

        //Create Contact Address
        $leadContactAddress = $this->contactAddresses()->create($contact->addresses->first()->toArray());
        $leadContactAddress->histories()->create($leadContactAddress->toArray());

        //Create Company CustomQuestion
        $company->customQuestions->map(function ($question) {
            $this->createCustomQuestions($question->toArray(), $this->leadCompanyCustomQuestions());
        });

        //Create Contact CustomQuestion
        $contact->customQuestions->map(function ($question) {
            $this->createCustomQuestions($question->toArray(), $this->leadContactCustomQuestions());
        });
    }

    /**
     * Update Lead And Related Model.
     *
     * @param  array $data
     * @param  self  $lead
     * @return void
     */
    public function updateLead(array $data, self $lead): void
    {
        //Update Lead
        $data['lead'] = array_except($data['lead'], ['uuid']);
        $lead->fill($data['lead']);
        $lead->save();

        if ($lead->getChanges()) {
            $lead->histories()->delete();
            $lead->histories()->create($data['lead']);
        }

        //Update LeadCompany & LeadContact Address Model
        $this->updateRelatedAddressModel($lead->companyAddresses->first(), $data['company']->addresses->first()->toArray());
        $this->updateRelatedAddressModel($lead->contactAddresses->first(), $data['contact']->addresses->first()->toArray());

        //Update LeadCompany & LeadContact Custom Question
        $this->updateCustomQuestions($lead->leadCompanyCustomQuestions, $data['company']->customQuestions->toArray(), $lead->leadCompanyCustomQuestions());
        $this->updateCustomQuestions($lead->leadContactCustomQuestions, $data['contact']->customQuestions->toArray(), $lead->leadContactCustomQuestions());
    }

    /**
     * Update LeadCompany & LeadContact Address Model.
     * @param  [type] $model
     * @param  array  $data
     * @return void
     */
    public function updateRelatedAddressModel($model, array $data): void
    {
        $model->fill($data);
        $model->save();
        if ($model->getChanges()) {
            $model->histories()->delete();
            $model->histories()->create(array_add($data, 'lead_id', $model->lead_id));
        }
    }

    /**
     * Create LeadCompany & LeadContact Custom Question.
     *
     * @param  [type] $question
     * @param  [type] $model
     * @return void
     */
    public function createCustomQuestions($question, $model): void
    {
        $leadCustomQuestion = $model->create($question);

        $leadCustomQuestion->histories()->create($leadCustomQuestion->toArray());
    }

    /**
     * Update LeadCompany & LeadContact Custom Question.
     *
     * @param  [type] $model
     * @param  array  $questions
     * @param  mixed  $lead
     * @return void
     */
    public function updateCustomQuestions($model, array $questions, $lead): void
    {
        $questions = [
            'custom_questions' => $questions,
        ];

        $this->createOrUpdateCustomQuestions($questions, $model, $lead);
    }

    /**
     * Prepare Lead Data Set.
     *
     * @param  int   $companyId
     * @param  int   $contactId
     * @param  array $data
     * @return array
     */
    public function prepareLeadDataSet(int $companyId, int $contactId, array $data): array
    {
        // Company data for Lead
        $companyCollection = collect($data['company'])->except($this->getExcludeColumns());
        $company = $this->keyChange('company_', $companyCollection, ['company_size', 'company_size_pick_list'])->all();
        $company['company_size'] = $data['company']['company_size'] ?? '';
        $company['company_size_pick_list'] = $data['company']['company_size_pick_list'] ?? '';

        // Contact data for Lead
        $contactCollection = collect($data['contact'])->except($this->getExcludeColumns());
        $contact = $this->keyChange('contact_', $contactCollection, ['company_id'])->all();

        // Inject source and created_at in Lead data
        $lead = $this->injectSource(array_merge($company, $contact), array_only($data, ['source', 'created_at']));

        $lead['incoming_company_id'] = $data['company']['id'] ?? null;
        $lead['incoming_contact_id'] = $data['contact']['id'] ?? null;
        $lead['uuid'] = $data['lead_uuid'];
        $lead['company_id'] = $companyId;
        $lead['contact_id'] = $contactId;

        return $lead;
    }

    /**
     * Change Company And Contact Field Name.
     *
     * @param string $keyVal
     * @param []     $data
     * @param array  $exceptColumn
     */
    public function keyChange(string $keyVal, $data, array $exceptColumn)
    {
        return $data->except($exceptColumn)->mapWithKeys(function ($item, $key) use ($keyVal) {
            return [$keyVal . $key => $item];
        });
    }
}
