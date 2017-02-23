<?php

namespace Tests;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Sympla\Auth\Client;
use Sympla\Auth\PasswordClient;

class GetUserTest extends TestCase
{
    /**
     * @test
     * @expectedException \Sympla\Auth\Exception\InvalidCredentialsException
     */
    public function try_to_get_an_user_without_an_access_token()
    {
        $client = new PasswordClient(new \GuzzleHttp\Client(), 'test', 'test', '', '');
        $user = $client->getUser('');
    }

    /**
     * @test
     * @expectedException \Sympla\Auth\Exception\InvalidCredentialsException
     */
    public function try_to_get_an_user_without_an_access_token_from_request()
    {
        $request = new Request('GET', 'endpoint', [], '');

        $client = new PasswordClient(new \GuzzleHttp\Client(), 'test', 'test', '', '');
        $user = $client->getUser($request);
    }
}
