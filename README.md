# sympla/mesa-gold-bar

This library helps identifying users by their access tokens.

## Installation

Install the package using composer:

    $ composer require sympla/oauth-remote-authentication ~1.0
    
That's it.

## Usage

The authenticator gets two parameters in its constructor: a Guzzle client and an
authentication endpoint, as a string. Once built, you can identify an user 
using either the raw token or a PSR-7 request object:

```php
<?php 

require_once "vendor/autoload.php";

use Sympla\RemoteAuthentication\OAuth2RemoteAuthentication;

$authenticator = new OAuth2RemoteAuthentication(
    new GuzzleHttp\Client,
     'https://account.sympla.com.br/api/user/me'
);

//Gets the user from the request object
$request = Request::createFromGlobals(); // hydrates the psr-7 request object
$user = $authenticator->getUserFromRequest($request); 
// Or, alternatively, gets the user from the token directly:
$token = explode(" ", $_SERVER['HTTP_AUTHORIZATION'])[1];
$user = $authenticator->getUserFromToken($token);

var_dump($user); // dumps information fetched from the endpoint server about the user.

```

## Author

Pedro Cordeiro <pedro.cordeiro@sympla.com.br>