<?php
namespace Webelightdev\LaravelSms\Drivers;

use Twilio\Rest\Client;
use Webelightdev\LaravelSms\Contract\MasterDriverSms;

class TwilioSms extends MasterDriverSms
{
    /**
     * Twilio Settings.
     *
     * @var null|object
     */
    protected $settings = null;

    /**
     * Twilio Client.
     *
     * @var null|Client
     */
    protected $client = null;

    /**
     * Construct the class with the relevant settings.
     *
     * SendSmsInterface constructor.
     * @param $settings object
     */
    public function __construct()
    {
        $this->config = config('sms');
        $this->driver = $this->config['default'];
        $this->twilioSettings = $this->config['drivers'][$this->driver];
        $this->client = new Client($this->twilioSettings['sid'], $this->twilioSettings['token']);
    }

    /**
     * Send text message and return response.
     *
     * @return object
     */
    public function send($mobileNum, $message)
    {
        $sms = $this->client->account->messages->create(
            $mobileNum,
            [
                'from' => $this->twilioSettings['from'],
                'body' => $message
            ]
        );

        $response['data'][$mobileNum] = $this->getSmsResponse($sms);
        return (object)$response;
    }

    /**
     * Get the Twilio Response.
     *
     * @param $sms
     * @return object
     */
    protected function getSmsResponse($sms)
    {
        $attributes = [
            'accountSid', 'apiVersion', 'body', 'direction', 'errorCode',
            'errorMessage', 'from', 'numMedia', 'numSegments', 'price',
            'priceUnit', 'sid', 'status', 'subresourceUris', 'to', 'uri',
            'dateCreated', 'dateUpdated', 'dateSent',
        ];

        $res = [];
        foreach ($attributes as $attribute) {
            $res[$attribute] = $sms->$attribute;
        }

        return (object)$res;
    }
}
