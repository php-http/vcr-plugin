<?php

namespace Http\Client\Plugin\Vcr;

use GuzzleHttp\Psr7\Response;
use Http\Client\Plugin\Vcr\Exception\NotFound;
use Http\Promise\FulfilledPromise;
use Http\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers Http\Client\Plugin\Vcr\VcrPlugin
 */
class VcrPluginTest extends VcrTestCase
{
    /**
     * @var VcrPlugin
     */
    private $plugin;

    /**
     * @var Recorder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vcr;

    /**
     * @var Tape|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tape;

    /**
     * @var Track|\PHPUnit_Framework_MockObject_MockObject
     */
    private $track;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    protected function setUp()
    {
        $this->request = $this->getMock(RequestInterface::class);

        $this->track = $this->getMockBuilder(Track::class)->disableOriginalConstructor()->getMock();
        $this->track
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->tape = $this->getMockBuilder(Tape::class)->disableOriginalConstructor()->getMock();
        $this->tape
            ->expects($this->any())
            ->method('findTrackByRequest')
            ->with($this->request)
            ->willReturn($this->track);

        $this->vcr = $this->getMockBuilder(Vcr::class)->disableOriginalConstructor()->getMock();

        $this->plugin = new VcrPlugin($this->vcr);
    }

    public function testReplayResponse()
    {
        $this->track->expects($this->any())->method('hasResponse')->willReturn(true);
        $this->track
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn($response = new Response(200))
        ;

        $this->vcr->expects($this->any())->method('isTurnedOn')->willReturn(true);
        $this->vcr->expects($this->any())->method('hasTape')->willReturn(true);
        $this->vcr->expects($this->any())->method('getTape')->willReturn($this->tape);

        $promise = $this->plugin->handleRequest($this->request, $this->fulfilledPromise($response), $this->rejectedPromise());

        /** @var ResponseInterface $returnedResponse */
        $returnedResponse = $promise->wait();

        $this->assertInstanceOf(ResponseInterface::class, $returnedResponse);
        $this->assertTrue($returnedResponse->hasHeader(VcrPlugin::HEADER_VCR));
        $this->assertEquals(VcrPlugin::HEADER_VCR_REPLAY, $returnedResponse->getHeaderLine(VcrPlugin::HEADER_VCR));
    }

    public function testReplayException()
    {
        $this->track->expects($this->any())->method('hasResponse')->willReturn(false);
        $this->track->expects($this->any())->method('hasException')->willReturn(true);

        $exception = new \Exception();

        $this->track
            ->expects($this->any())
            ->method('getException')
            ->willReturn($exception)
        ;

        $this->vcr->expects($this->any())->method('isTurnedOn')->willReturn(true);
        $this->vcr->expects($this->any())->method('hasTape')->willReturn(true);
        $this->vcr->expects($this->any())->method('getTape')->willReturn($this->tape);

        $promise = $this->plugin->handleRequest(
            $this->request,
            $this->fulfilledPromise($this->getMock(ResponseInterface::class)),
            $this->rejectedPromise($exception)
        );

        $this->expectException(\Exception::class);

        $promise->wait();
    }

    public function testDoNothingIfTurnedOff()
    {
        $this->vcr->expects($this->any())->method('isTurnedOn')->willReturn(false);

        $promise = $this->plugin->handleRequest(
            $this->request,
            $this->fulfilledPromise($response = new Response(200)),
            $this->rejectedPromise()
        );

        $this->assertSame($response, $promise->wait());
    }

    public function testDoNothingIfNotRecording()
    {
        $this->vcr->expects($this->any())->method('isTurnedOn')->willReturn(false);
        $this->vcr->expects($this->any())->method('hasTape')->willReturn(false);
    }

    public function testRecordRequestIfNotOnTape()
    {
        $this->vcr->expects($this->any())->method('isTurnedOn')->willReturn(true);
        $this->vcr->expects($this->any())->method('hasTape')->willReturn(true);
        $this->vcr->expects($this->any())->method('getTape')->willReturn($this->tape);

        $this->tape->expects($this->once())->method('findTrackByRequest')->willThrowException(new NotFound());

        $this->tape->expects($this->once())->method('addTrack');

        $this->plugin->handleRequest($this->request, $this->fulfilledPromise(), $this->rejectedPromise());
    }

    public function testRecordResponseIfNotOnTape()
    {
        $this->track->expects($this->any())->method('hasResponse')->willReturn(false);
        $this->track->expects($this->any())->method('hasException')->willReturn(false);

        $this->vcr->expects($this->any())->method('isTurnedOn')->willReturn(true);
        $this->vcr->expects($this->any())->method('hasTape')->willReturn(true);
        $this->vcr->expects($this->any())->method('getTape')->willReturn($this->tape);

        $this->tape->expects($this->once())->method('findTrackByRequest')->willReturn($this->track);

        $this->tape->expects($this->once())->method('addTrack');

        $this->plugin->handleRequest($this->request, $this->fulfilledPromise(), $this->rejectedPromise());
    }

    private function fulfilledPromise(ResponseInterface $response = null)
    {
        $response = $response ?: new Response(200);

        return function () use ($response) {
            return new FulfilledPromise($response);
        };
    }

    private function rejectedPromise(\Exception $e = null)
    {
        $e = $e ?: new \Exception();

        return function () use ($e) {
            if ($e instanceof \Exception) {
                return new RejectedPromise($e);
            }
        };
    }
}
