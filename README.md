# OAuth Remote Authentication library

Essa biblioteca visa auxiliar na identificação de usuários através de seus 
Access Tokens.

## Instalação

Certifique-se de que `packagist.com` esteja adicionado à lista de repositórios
do seu `composer.json`:

```json
    {
        "repositories": [
            {"type": "composer", "url": "https://repo.packagist.com/sympla/"},
            {"packagist": false}
        ]
    }
```

Então, instale o pacote:

    $ composer require sympla/oauth-remote-authentication ~1.0
    
É isso.

## Utilização

O autenticador recebe dois parâmetros em seu construtor: um Guzzle client e uma 
string com o endpoint de autenticação. Uma vez construído o objeto, você pode 
identificar um usuário a partir de um PSR-7 request object ou a partir de um
token, diretamente:

```php
<?php 

require_once "vendor/autoload.php";

use Sympla\RemoteAuthentication\OAuth2RemoteAuthentication;

$authenticator = new OAuth2RemoteAuthentication(
    new GuzzleHttp\Client,
     'https://account.sympla.com.br/api/user/me'
);

//Gets the user from the request object
$request = Request::createFromGlobals(); // hydrates the request object
$user = $authenticator->getUserFromRequest($request); 
// Or, alternatively, gets the user from the token directly:
$token = explode(" ", $_SERVER['HTTP_AUTHORIZATION'])[1];
$user = $authenticator->getUserFromToken($token);

var_dump($user); // dumps information fetched from the endpoint server about the user.

```

## Autor

Pedro Cordeiro <pedro.cordeiro@sympla.com.br>