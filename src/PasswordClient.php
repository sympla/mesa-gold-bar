<?php

namespace Sympla\Auth;

use Firebase\JWT\JWT;
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
        string $authMethod = 'get',
        string $clientProvider = '',
        string $clientDomain = '',
        string $clientServiceAccount = '',
        string $publicKeyPath = ''
    ) {
        $this->guzzle = $httpClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->userEndpoint = $userInformationEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->authMethod = $authMethod;
        $this->clientProvider = $clientProvider;
        $this->clientDomain = $clientDomain;
        $this->clientServiceAccount = $clientServiceAccount;
        $this->publicKeyPath = $publicKeyPath;
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

        if ($this->isJWT($accessToken)) {
            if ($response = $this->validateJWT($accessToken)) {
                return $response;
            }
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
                $tokenInfo = json_decode((string)$response->getBody(), true);
                $this->checkClientProvider($tokenInfo);
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

    /**
     * @param $accessToken
     */
    public function isJWT($accessToken)
    {
        $tks = explode('.', $accessToken);
        if (count($tks) === 3) {
            return true;
        }
        return false;
    }

    /**
     * @param $accessToken
     * @throws Exception\InvalidCredentialsException
     */
    public function validateJWT($accessToken)
    {
        try {
            if (empty($this->publicKeyPath)) {
                return false;
            }
            
            $publicKey = file_get_contents(resource_path($this->publicKeyPath));
            $decoded = JWT::decode($accessToken, $publicKey, array('RS256'));

            if (true === $decoded->exp < time()) {
                throw new Exception\InvalidCredentialsException(
                    'Token has expired'
                );
            }

            $accessToken = $decoded->oauth->credentials->access_token;
            return (array) $decoded->oauth->profile;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            throw new Exception\InvalidCredentialsException($error);
        }
    }

    /**
     * @param $tokenInfo
     */
    public function checkClientProvider($tokenInfo)
    {
        switch (strtolower($this->clientProvider)) {
            // Google
            case 'google':
                if (!isset($tokenInfo['email'])) {
                    throw new Exception\InvalidCredentialsException(
                        'Invalid token - Email not found'
                    );
                }

                list($user, $domain) = explode('@', $tokenInfo['email']);
                
                // Check hosted domain
                if ($domain !== $this->clientDomain && $tokenInfo['email'] !== $this->clientServiceAccount) {
                    throw new Exception\InvalidCredentialsException(
                        'Mismatch domain'
                    );
                }
                break;
            
            default:
                # code...
                break;
        }
    }
}
