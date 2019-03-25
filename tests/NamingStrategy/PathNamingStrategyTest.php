<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Tests\NamingStrategy;

use GuzzleHttp\Psr7\Request;
use Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
class PathNamingStrategyTest extends TestCase
{
    /**
     * @dataProvider provideRequests
     */
    public function testName(string $expected, RequestInterface $request, array $options = []): void
    {
        $strategy = new PathNamingStrategy($options);

        $this->assertSame($expected, $strategy->name($request));
    }

    public function provideRequests(): \Generator
    {
        yield 'Simple GET request' => ['GET_my-path_my-sub-path', $this->getRequest('/my-path/my-sub-path')];

        yield 'GET request with query' => ['GET_my-path_2fb8f', $this->getRequest('/my-path?foo=bar')];

        yield 'GET request with different query' => ['GET_my-path_daa84', $this->getRequest('/my-path?foo=baz')];

        yield 'GET request with hostname' => [
            'example.org_GET_my-path',
            $this->getRequest('https://example.org/my-path'),
        ];

        yield 'Header hash' => ['GET_my-path_4727a', $this->getRequest('/my-path'), ['hash_headers' => ['X-Foo']]];

        yield 'Body hash' => ['POST_my-action_d3b09', $this->getRequest('/my-action', 'POST', '{"hello": "world"}')];

        yield 'Method excluded' => [
            'POST_my-action',
            $this->getRequest('/my-action', 'POST', '{"hello": "world"}'),
            ['hash_body_methods' => []],
        ];

        yield 'Full package' => [
            'POST_my-action_4727a_1a3b6_d3b09',
            $this->getRequest('/my-action?page=1', 'POST', '{"hello": "world"}'),
            ['hash_headers' => ['X-Foo']],
        ];
    }

    private function getRequest(string $uri, string $method = 'GET', ?string $body = null): RequestInterface
    {
        return new Request($method, $uri, ['X-Foo' => 'Bar'], $body);
    }
}
