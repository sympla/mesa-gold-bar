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
        $this->publishes([$this->configPath() => config_path('sympla.php')]);

        $auth = $this->app->make('auth');

        $auth->provider('oauth', function ($app, array $config) {
            return new OAuthUserProvider(
                $app->make('sympla.oauth.password_client'),
                $config,
                $app->make('request')
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
                $config['user_endpoint'],
                $config['token_endpoint'],
                $config['auth_method'],
                $config['client_provider'],
                $config['client_domain'],
                $config['client_service_account']
            );
        });
    }

    protected function configPath()
    {
        return __DIR__ . '/../../config/sympla.php';
    }
}
