<?php

namespace Tests;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Sympla\Auth\Client;

class LoginController extends TestCase
{
    /**
     * @test
     */
    public function get_an_access_token_using_password_credentials()
    {
        $response = [
            "access_token" => "ODY4MjUxY2M2MmE3YTI4OGJkYmU1ZGU3YTI5NWQwMjMwZWQzMTJhYjVjY2JjMDA2MTlkOTMwYjgxZGU2NWE3Ng",
            "expires_in" => "28800",
            "token_type" => "bearer",
            "scope" => "read",
        ];

        $mock = new MockHandler([
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($response)),
        ]);

        $handler = HandlerStack::create($mock);

        $container = [];
        $history = Middleware::history($container);
        $handler->push($history);

        $guzzle = new Guzzle(['handler' => $handler]);

        $client = new Client('client_id', 'client_secret');

        $inspector = new \ReflectionObject($client);
        $property = $inspector->getProperty('guzzle');
        $property->setAccessible(true);
        $property->setValue($client, $guzzle);

        $expected = $client->login('username', 'password');

        $this->assertCount(1, $container);

        $request = $container[0]['request'];

        $this->assertEquals('POST', $request->getMethod());

        $body = [];

        parse_str((string)$request->getBody(), $body);

        $expected = [
            'grant_type' => 'password',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'username' => 'username',
            'password' => 'password',
        ];

        $this->assertEquals($expected, $body);
    }
}
