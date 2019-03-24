<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Client\Plugin\Vcr\ReplayPlugin;
use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class ReplayPluginTest extends AbstractPluginTestCase
{
    public function testHandleRequest(): void
    {
        $this->namingStrategy->method('name')->willReturn('foo');
        $next = function (): Promise {
            return new FulfilledPromise(new Response(200, [], 'not replayed'));
        };

        $this->plugin->handleRequest($this->getRequest(), $next, $this->failCallback())
            ->then(function (ResponseInterface $response): void {
                $this->assertFalse($response->hasHeader(ReplayPlugin::HEADER_NAME), 'Header should not be added');
                $this->assertSame('not replayed', (string) $response->getBody());
            });

        $this->recorder->record('foo', new Response(200, [], 'Replayed'));

        $this->plugin->handleRequest($this->getRequest(), $this->failCallback(), $this->failCallback())
            ->then(function (ResponseInterface $response): void {
                $this->assertTrue($response->hasHeader(ReplayPlugin::HEADER_NAME), 'A header should be added');
                $this->assertSame(['foo'], $response->getHeader(ReplayPlugin::HEADER_NAME));
                $this->assertSame('Replayed', (string) $response->getBody());
            });
    }

    protected function getPluginClass(): string
    {
        return ReplayPlugin::class;
    }
}
