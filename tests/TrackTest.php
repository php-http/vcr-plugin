<?php

namespace Http\Client\Plugin\Vcr;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Http\Client\Plugin\Vcr\Track
 */
class TrackTest extends VcrTestCase
{
    /**
     * @var Track
     */
    private $track;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Exception|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exception;

    protected function setUp()
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->exception = new \Exception();

        $this->track = Track::create($this->request);
    }

    public function testCreateTrack()
    {
        $this->assertSame($this->request, $this->track->getRequest());

        $this->assertFalse($this->track->hasResponse());
        $this->assertNull($this->track->getResponse());

        $this->assertFalse($this->track->hasException());
        $this->assertNull($this->track->getException());
    }

    public function testWithResponse()
    {
        $this->track->setResponse($this->response);

        $this->assertTrue($this->track->hasResponse());
        $this->assertSame($this->response, $this->track->getResponse());
    }

    public function testWithException()
    {
        $this->track->setException($this->exception);

        $this->assertTrue($this->track->hasException());
        $this->assertSame($this->exception, $this->track->getException());
    }

    public function testResponseBodyIsRewound()
    {
        $body = $this->createMock(StreamInterface::class);
        $this->response->expects($this->once())->method('getBody')->willReturn($body);

        $this->track->setResponse($this->response);

        $body->expects($this->once())->method('seek')->with(0, SEEK_SET);
        $this->track->getResponse();
    }
}
