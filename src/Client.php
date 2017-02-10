<?php

namespace Sympla\Auth;

use GuzzleHttp\Client as Guzzle;
use Psr\Http\Message\RequestInterface;
use Sympla\Auth\Exception;

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

    private function getAccessTokenFrom(RequestInterface $request) : string
    {
        if (false === $request->hasHeader('Authorization')) {
            throw new Exception\InvalidCredentialsException(
                'There needs to be an Authorization header with a Bearer token to access this route.'
            );
        }

        $token = $request->getHeader('Authorization');
        if (false === preg_match('/Bearer/', $token)) {
            throw new InvalidCredentialsException(
                'The information passed in the Authorization header is not a valid Bearer token.'
            );
        }

        return $token;
    }

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
            $response = $this->guzzle->get(self::ENDPOINT_USER, [
                'headers' => [
                    'Authorization' => $accessToken
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true)['error_description'];
            throw new InvalidCredentialsException($error);
        }
    }
}
