<?php

namespace Sympla\Auth\Laravel;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Sympla\Auth\PasswordClient;

class OAuthUserProvider implements UserProvider
{
    private $passwordClient;

    public function __construct(PasswordClient $passwordClient)
    {
        $this->passwordClient = $passwordClient;
    }

    public function retrieveById($identifier)
    {
        // not used
    }
    public function retrieveByToken($identifier, $token)
    {
        // no used
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // not used
    }

    public function retrieveByCredentials(array $credentials)
    {
        return $this->passwordClient->getUser(
            $credentials['api_token']
        );
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // not used
    }
}
