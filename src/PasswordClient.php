<?php

namespace Sympla\Auth;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Sympla\Auth\Exception;

class PasswordClient
{
    const ENDPOINT_TOKEN = 'oauth/v2/token';
    const ENDPOINT_USER = 'api/user/me';

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var Guzzle */
    private $guzzle;

    /** @var string */
    private $tokenEndpoint;

    /** @var  string */
    private $userEndpoint;

    /**
     * PasswordClient constructor.
     * @param Guzzle $httpClient
     * @param string $clientId
     * @param string $clientSecret
     * @param string $userEndpoint
     * @param string $tokenEndpoint
     */
    public function __construct(
        Guzzle $httpClient,
        string $clientId,
        string $clientSecret,
        string $userEndpoint,
        string $tokenEndpoint
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->userEndpoint = $userEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->guzzle = $httpClient;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    public function login(string $email, string $password) : array
    {
        $response = $this->guzzle->post($this->tokenEndpoint, [
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

    private function getAccessTokenFrom(RequestInterface $request) : string
    {
        if (false === $request->hasHeader('Authorization')) {
            throw new Exception\InvalidCredentialsException(
                'There needs to be an Authorization header with a Bearer token to access this route.'
            );
        }

        $token = $request->getHeader('Authorization');
        if (false === preg_match('/Bearer/', $token)) {
            throw new Exception\InvalidCredentialsException(
                'The information passed in the Authorization header is not a valid Bearer token.'
            );
        }

        return $token;
    }

    /**
     *
     *
     * @param $accessToken
     * @return array
     * @throws Exception\InvalidCredentialsException
     */
    public function getUser($accessToken) : array
    {
        if ($accessToken instanceof RequestInterface) {
            $accessToken = $this->getAccessTokenFrom($accessToken);
        }

        if (true === empty($accessToken)) {
            throw new Exception\InvalidCredentialsException(
                'You must provide a token to get informations about an user'
            );
        }

        if (false === preg_match('/Bearer/', $accessToken)) {
            $accessToken = "Bearer ${accessToken}";
        }

        try {
            $response = $this->guzzle->get($this->userEndpoint, [
                'headers' => [
                    'Authorization' => $accessToken
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true)['error_description'];
            throw new Exception\InvalidCredentialsException($error);
        }
    }
}
