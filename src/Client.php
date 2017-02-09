<?php

namespace Sympla\Auth;

use GuzzleHttp\Client as Guzzle;

class Client
{
    const ENDPOINT_TOKEN = 'oauth/v2/token';
    const ENDPOINT_USER = 'api/user/me';

    /**dd
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var Guzzle
     */
    private $guzzle;

    /**
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $this->guzzle = new Guzzle([
            'base_uri' => 'https://accounts.sympla.com.br',
            'timeout' => 2,
        ]);
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    public function login(string $email, string $password) : array
    {
        $response = $this->guzzle->post(self::ENDPOINT_TOKEN, [
            'form_params' => [
                'username' => $email,
                'password' => $password,
                'grant_type' => 'password',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }
}
