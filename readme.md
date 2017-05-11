# mesa-gold-bar

Library for authenticate our clients through OAuth

## Install

To install, use composer:

    $ composer require sympla/mesa-bar-gold


## Using with Laravel

After install it, register the service provider into your Laravel application
into `config/app.php`:

    Sympla\Auth\Laravel\ServiceProvider::class


Then, publish the configuration:

    $ php artisan vendor:publish --provider="Sympla\Auth\Laravel\ServiceProvider"

Once your application is configured, go to `config/auth.php` and change the `api` section:

```
     'guards' => [
         'api' => [
             'driver' => 'token',
             'provider' => 'oauth',
         ],
     ],
```

Then, activate the `auth:api` as a middleware for your api.
