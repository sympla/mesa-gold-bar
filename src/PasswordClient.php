<?php

namespace Sympla\Auth;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Sympla\Auth\Exception;

class PasswordClient
{
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
     * @param Guzzle $httpClient                A Guzzle ClientInterface
     * @param string $clientId                  The OAuth client_id
     * @param string $clientSecret              The OAuth client_secret
     * @param string $userInformationEndpoint   A full URL to fetch user information from its token
     * @param string $tokenEndpoint             The endpoint to fetch oauth tokens
     * @param string $authMethod                The auth method
     */
    public function __construct(
        Guzzle $httpClient,
        string $clientId = '',
        string $clientSecret = '',
        string $userInformationEndpoint = '',
        string $tokenEndpoint = '',
        string $authMethod = 'get'
    ) {
        $this->guzzle = $httpClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->userEndpoint = $userInformationEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->authMethod = $authMethod;
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

        if ($this->authMethod == 'header' && false == preg_match('/Bearer/', $accessToken)) {
            $accessToken = "Bearer ${accessToken}";
        }

        try {
            if ($this->authMethod == 'header') {
                $response = $this->guzzle->get($this->userEndpoint, [
                    'headers' => [
                        'Authorization' => $accessToken
                    ]
                ]);
            } else {
                $response = $this->guzzle->get($this->userEndpoint.'?access_token='.$accessToken);
            }

            return json_decode((string)$response->getBody(), true);
        } catch (RequestException $e) {
            $error = $e->getMessage();
            if ($response = $e->getResponse()) {
                $error = json_decode((string)$response->getBody(), true)['error_description'];
            }

            throw new Exception\InvalidCredentialsException($error);
        }
    }
}
