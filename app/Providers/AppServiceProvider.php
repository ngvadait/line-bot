<?php

namespace App\Providers;

use LINE\LINEBot;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // LINE BOT
        $this->app->bind('line-bot', function ($app, array $parameters) {
            return new LINEBot(
                new CurlHTTPClient(env('LINE_ACCESS_TOKEN')),
                ['channelSecret' => env('LINE_CHANNEL_SECRET')]
            );
        });
    }
}
