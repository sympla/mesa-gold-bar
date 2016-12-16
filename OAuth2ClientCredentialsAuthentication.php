<?php

namespace Sympla\ClientAuthentication;

use Sympla\ClientAuthentication\Exception\InvalidCredentialsException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class OAuth2ClientCredentialsAuthentication
 * @package Authentication
 */
class OAuth2ClientCredentialsAuthentication
{
    /** @var ContainerInterface */
    private $container = null;
    /** @var array */
    private $options = [];

    public function __construct(ContainerInterface $container, array $options = [])
    {
        $this->container = $container;
        $this->options = $options;
    }

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        //No paths protected, everything is public. Do not authenticate.
        if (!isset($this->options["protected"])) {
            return $next($request, $response);
        }

        $uri = $request->getUri();
        $protected = false;

        foreach ($this->options["protected"] as $routePattern) {
            if (preg_match($routePattern, $uri)) {
                $protected = true;
                break;
            }
        }

        // Route ain't protected. No need to authenticate.
        if (!$protected) {
            return $next($request, $response);
        }

        $token = $request->getHeaderLine('Authorization');

        try {
            if (empty($token)) {
                throw new InvalidCredentialsException(
                    'There needs to be an Authorization header with a Bearer token to access this route.'
                );
            }

            $params = explode(" ", $token);
            if ($params[0] !== 'Bearer' || count($params) !== 2) {
                throw new InvalidCredentialsException(
                    'The information passed in the Authorization header is not a valid Bearer token.'
                );
            }

            $accessToken = $params[1];

            $client = new Client;
            try {
                $response = $client->get(
                    $this->options['authentication_server'],
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken
                        ]
                    ]
                );
            } catch (RequestException $e) {
                $error = json_decode($e->getResponse()->getBody()->getContents(), true)['error_description'];
                throw new InvalidCredentialsException($error);
            }

            $user = json_decode($response->getBody()->getContents(), true);
            $this->container['user'] = $user;

            return $next($request, $response);
        } catch (InvalidCredentialsException $e) {
            $response
                ->getBody()
                ->write($e->getMessage() . "\n");

            return $response->withStatus(401);
        }
    }
}
