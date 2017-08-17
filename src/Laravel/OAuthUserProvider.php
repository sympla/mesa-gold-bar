<?php

namespace Sympla\Auth\Laravel;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Sympla\Auth\PasswordClient;
use Sympla\Auth\Exception;

class OAuthUserProvider implements UserProvider
{
    private $passwordClient;

    private $config;

    private $request;
    
    public function __construct(PasswordClient $passwordClient, array $config, Request $request)
    {
        $this->passwordClient = $passwordClient;
        $this->config = $config;
        $this->request = $request;
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

    public function createUser($model, $data)
    {
        $data['name'] = $data['email'];
        $data['password'] = Hash::make(str_random(10));
        $user = new $model($data);
        $user->save();
        return $user;
    }

    public function retrieveByCredentials(array $credentials)
    {
        try {
            $data = $this->passwordClient->getUser(
                $credentials['api_token']
            );

            $this->request->credentials = $data;

            if (true === array_key_exists('model', $this->config)) {
                $model = $this->config['model'];
                
                if ($user = $model::whereEmail($data['email'])->first()) {
                    return $user;
                }

                return self::createUser($model, $data);
            }
            return $data;
        } catch (Exception\InvalidCredentialsException $e) {
            throw new AuthenticationException($e->getMessage());
        }
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // not used
    }
}
