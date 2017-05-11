<?php

namespace Sympla\Auth\Laravel;

use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Sympla\Auth\Laravel\OAuthUserProvider;
use Sympla\Auth\PasswordClient;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $auth = $this->app->make('auth');

        $auth->provider('oauth', function ($app, array $config) {
            return new OAuthUserProvider(
                $app->make('sympla.oauth.password_client')
            );
        });
    }

    public function register()
    {
        $this->app->bind('sympla.oauth.password_client', function ($app) {
            $config = $app->config['sympla']['oauth'];

            return new PasswordClient(
                new Guzzle(),
                $config['client_id'],
                $config['client_secret'],
                $config['user_endpoint']
            );
        });
    }
}
