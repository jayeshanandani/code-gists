
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This value determines which of the following gateway to use.
    | You can switch to a different driver at runtime.
    |
 */
    'default' => 'twilio_sms',
    /*
    |--------------------------------------------------------------------------
    | List of Drivers
    |--------------------------------------------------------------------------
    |
    | These are the list of drivers to use for this package.
    | You can change the name. Then you'll have to change
    | it in the map array too.
    |
     */
    'drivers' => [
        'twilio_sms' => [
            'sid' => env('TWILIO_ACCOUNT_SID'),
            'token' => env('TWILIO_ACCOUNT_TOKEN'),
            'key' => env('TWILIO_API_KEY'),
            'secret' => env('TWILIO_API_SECRET'),
            'from' => env('TWILIO_NUMBER'),
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | Class Maps-v
    |--------------------------------------------------------------------------
    |
    | This is the array of Classes that maps to Drivers above.
    | You can create your own driver if you like and add the
    | config in the drivers array and the class to use for
    | here with the same name. You will have to extend
    | webelightdev\laravelSms\Contract\MasterDriver in your driver.
    |
     */
    'map' => [
        'twilio_sms' => Webelightdev\LaravelSms\Drivers\TwilioSms::class,
    ],

    'moduleEnable' => [
        'sms' => true,
    ]

];