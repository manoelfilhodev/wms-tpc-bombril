<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Graph\GraphExtendSocialite;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SocialiteWasCalled::class => [
            GraphExtendSocialite::class . '@handle',
        ],
    ];
}
