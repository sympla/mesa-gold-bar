<?php

namespace Sympla\Auth\Lumen;

use Sympla\Auth\Laravel\OAuthUserProvider;
use Sympla\Auth\Laravel\ServiceProvider as LumenServiceProvider;

class ServiceProvider extends LumenServiceProvider
{
    public function boot()
    {
        $this->publishes([$this->configPath() => $this->appConfigPath('sympla.php')]);

        $auth = $this->app->make('auth');

        $auth->provider('oauth', function ($app, array $config) {
            return new OAuthUserProvider(
                $app->make('sympla.oauth.password_client'),
                $config,
                $app->make('request')
            );
        });
    }

    protected function appConfigPath($path)
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}
