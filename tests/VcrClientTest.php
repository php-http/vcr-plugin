<?php

namespace Http\Client\Plugin\Vcr;

use Http\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers Http\Client\Plugin\Vcr\VcrClient
 */
class VcrClientTest extends VcrTestCase
{
    /**
     * @var VcrClient
     */
    private $vcrClient;

    /**
     * @var ClientImplementation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var Vcr|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vcr;

    protected function setUp()
    {
        $this->vcr = $this->getMockBuilder(Vcr::class)->getMock();
        $this->client = $this->getMock(ClientImplementation::class);
        $this->vcrClient = new VcrClient($this->client, $this->vcr);
    }

    public function testSendRequest()
    {
        $request = $this->getMock(RequestInterface::class);
        $response = $this->getMock(ResponseInterface::class);

        $this->client->expects($this->once())->method('sendRequest')->with($request)->willReturn($response);

        $this->assertSame($response, $this->vcrClient->sendRequest($request));
    }

    public function testSendAsyncRequest()
    {
        $request = $this->getMock(RequestInterface::class);
        $response = $this->getMock(ResponseInterface::class);
        $fulfilledPromise = new FulfilledPromise($response);

        $this->client->expects($this->once())->method('sendAsyncRequest')->with($request)->willReturn($fulfilledPromise);

        $promise = $this->vcrClient->sendAsyncRequest($request);

        $this->assertInstanceOf(FulfilledPromise::class, $promise);
        $this->assertSame($response, $promise->wait());
    }
}
