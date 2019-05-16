# laravel-sms
## Twilio support

```
composer require webelightdev/laravel-sms
```

```
"laravel": {
    "providers": [
        "Webelightdev\\LaravelSms\\SmsServiceProvider::class"
    ],
    "aliases": {
        "sms" : "Webelightdev\\LaravelSms\\Facade\\Sms"
    }
}
```

```
composer dump-autoload
```

```
php artisan vendor:publish
```

```
php artisan config:cache
```