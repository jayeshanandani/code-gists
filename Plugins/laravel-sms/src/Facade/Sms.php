<?php
namespace Webelightdev\LaravelSms\Facade;

use Illuminate\Support\Facades\Facade;


class Sms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'twilio_sms';
    }
}