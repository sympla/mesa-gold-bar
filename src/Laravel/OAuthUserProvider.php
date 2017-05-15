<?php

namespace Sympla\Auth\Laravel;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Sympla\Auth\PasswordClient;
use Sympla\Auth\Exception;

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
        try {
            return $this->passwordClient->getUser(
                $credentials['api_token']
            );
        } catch (Exception\InvalidCredentialsException $e) {
            throw new AuthenticationException;
        }
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // not used
    }
}
