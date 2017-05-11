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
        $this->publishes([$this->configPath() => config_path('cors.php')]);

        $auth = $this->app->make('auth');

        $auth->provider('oauth', function ($app, array $config) {
            return new OAuthUserProvider(
                $app->make('sympla.oauth.password_client')
            );
        });
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'sympla');

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

    protected function configPath()
    {
        return __DIR__ . '/../../config/sympla.php';
    }
}
