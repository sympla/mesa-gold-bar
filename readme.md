# sympla/mesa-gold-bar

> Mesa Gold is a resort and bar located within the Westworld Mesa Hub.
> It is used by the Guests to relax at the end of their visit to the Park.

This library helps identifying users by their access tokens.

## Installation

Install the package using composer:

    $ composer require sympla/mesa-gold-bar ~3.0

That's it.

## Usage

The authenticator gets two parameters in its constructor: a Guzzle client and an
authentication endpoint, as a string. Once built, you can identify an user
using either the raw token or a PSR-7 request object:

```php
<?php

require_once "vendor/autoload.php";

use Sympla\Auth\OAuth2RemoteAuthentication;

$authenticator = new OAuth2RemoteAuthentication(
    new GuzzleHttp\Client,
     'https://example.com/api/whoami'
);

//Gets the user from the request object
$request = Request::createFromGlobals(); // hydrates the psr-7 request object
$user = $authenticator->getUserFromRequest($request);
// Or, alternatively, gets the user from the token directly:
$token = explode(" ", $_SERVER['HTTP_AUTHORIZATION'])[1];
$user = $authenticator->getUserFromToken($token);

var_dump($user); // dumps information fetched from the endpoint server about the user.

```

## Middleware

This library now also has a SlimMiddleware for retrieving user credentials.

Just add the middleware:

```php
<?php
#middleware.php
$app->add(new \Sympla\Auth\Middleware\SlimMiddleware(
    new \GuzzleHttp\Client,
    $app->getContainer(),
    [
        'protected' => ['/^\/admin\//'], //This is a regex for the protected URIs
        'authentication_server' => getenv('USER_INFO_ENDPOINT') //This is where the middleware
                                                                //should try to fetch the user info from
    ]
));
```

Then, from any of the `protected` routes, you can simply fetch the `user` object
from your DIC:

```php
<?php
#routes.php
$app->get('/admin', function ($request, $response, $args) {
    $user = $this->get('user');
    var_dump($user);
});
```

## Using with Laravel

After install it, register the service provider into your Laravel application
into `config/app.php`:

    Sympla\Auth\Laravel\ServiceProvider::class

Then, publish the configuration:

    $ php artisan vendor:publish --provider="Sympla\Auth\Laravel\ServiceProvider"

Once your application is configured, go to `config/auth.php` and change the `guard` section:

```
     'guards' => [
         'api' => [
             'driver' => 'token',
             'provider' => 'oauth',
         ],
     ],
```

And the `providers` section:

```
    'providers' => [
        'oauth' => [
            'driver' => 'oauth'
            'model' => App\User::class,
        ],
    ],
```

Then, activate the `auth:api` as a middleware for your api.

## Using with Lumen

After install it, register the service provider, route middleware, Hash facade and enable eloquent into your Lumen application
into `bootstrap/app.php`:
    
    //Service provider
    $app->register(Sympla\Auth\Lumen\ServiceProvider::class);
    
    // Route middleware
    $app->routeMiddleware([
        'auth' => App\Http\Middleware\Authenticate::class,
    ]);
    
    //Hash facade
    $app->withFacades(true, ['Illuminate\Support\Facades\Hash' => 'Hash']);
    
    //Enable eloquent
    $app->withEloquent();

After register the service provider, install the package for publish in Lumen application
    
    $ composer require laravelista/lumen-vendor-publish

Then, publish the configuration:

    $ php artisan vendor:publish --provider="Sympla\Auth\Lumen\ServiceProvider"

Once your application is configured, go to `config/auth.php`, if the file does not exist just create it, and change/create the `guard` section:

```
     'guards' => [
         'api' => [
             'driver' => 'token',
             'provider' => 'oauth',
         ],
     ],
```

And the `providers` section:

```
    'providers' => [
        'oauth' => [
            'driver' => 'oauth'
            'model' => App\User::class,
        ],
    ],
```

## Contact

Pedro Igor <pedro.igor@sympla.com.br>

## License

This project is distributed under the MIT License. Check [LICENSE][LICENSE.md] for more information.
