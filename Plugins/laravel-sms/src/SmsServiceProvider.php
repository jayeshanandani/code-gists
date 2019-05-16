<?php

namespace Webelightdev\LaravelSms;

use Illuminate\Support\ServiceProvider;

use Webelightdev\LaravelSms\SmsManager;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Configurations that needs to be done by user.
         */
        // echo "a";exit;
        $this->publishes([
            __DIR__ . '/../config/sms.php' => config_path('sms.php'),
        ], 'config');
     
        /**
         * Bind to service container.
         */
        $this->app->singleton('twilio_sms', function () {
            return new SmsManager();
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
