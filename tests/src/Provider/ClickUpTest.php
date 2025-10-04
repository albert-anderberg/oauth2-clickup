<?php

namespace Albertanderberg\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Mockery as m;
use Psr\Http\Message\RequestInterface;

class ClickUpTest extends \PHPUnit\Framework\TestCase
{
    use QueryBuilderTrait;

    protected $provider;

    protected function setUp() : void
    {
        $this->provider = new \Albertanderberg\OAuth2\Client\Provider\ClickUp([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown() : void
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl([
            'approval_prompt' => []
        ]);
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayNotHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes()
    {
        $scopeSeparator = ',';
        $options = ['scope' => [uniqid(), uniqid()]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);
        $this->assertStringContainsString($encodedScope, $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/api', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/api/v2/oauth/token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        // Mock expected return format from ClickUp
        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('__toString')
            ->andReturn('{"access_token": "mock_access_token", "token_type": "Bearer"}');

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn($stream);

        $response->shouldReceive('getHeader')
            ->times(1)
            ->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->withArgs(function (RequestInterface $request) {
                $body = json_decode($request->getBody(), true);
                var_dump($body);
                return
                    $body['code'] === 'mock_authorization_code'
                    && $body['client_id'] === 'mock_client_id'
                    && $body['client_secret'] === 'mock_secret';
            })
            ->times(1)
            ->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
    }
}
