<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Client\Plugin\Vcr\RecordPlugin;
use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class RecordPluginTest extends AbstractPluginTestCase
{
    public function testHandleRequest()
    {
        $response = new Response();
        $this->namingStrategy->method('name')->willReturn('foo');
        $next = function () use ($response): Promise {
            return new FulfilledPromise($response);
        };

        $this->plugin->handleRequest($this->getRequest(), $next, $this->failCallback())->then(function (ResponseInterface $response) {
            $this->assertTrue($response->hasHeader(RecordPlugin::HEADER_NAME), 'A header should be added');
            $this->assertSame(['foo'], $response->getHeader(RecordPlugin::HEADER_NAME));
        });

        $this->assertSame($response, $this->recorder->replay('foo'));
    }

    protected function getPluginClass(): string
    {
        return RecordPlugin::class;
    }
}
