<?php

namespace Http\Client\Plugin\Vcr;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class VcrTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     *
     * @return Tape|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createTape($name)
    {
        $tape = $this->getMockBuilder(Tape::class)
            ->enableArgumentCloning()
            ->disableOriginalConstructor()
            ->getMock();

        $tape->expects($this->any())->method('getName')->willReturn($name);

        return $tape;
    }

    /**
     * @param RequestInterface       $request
     * @param ResponseInterface|null $response
     * @param \Exception|null        $exception
     *
     * @return Track|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createTrack(RequestInterface $request, ResponseInterface $response = null, \Exception $exception = null)
    {
        $track = $this->getMockBuilder(Track::class)->disableOriginalConstructor()->getMock();

        $track->expects($this->any())->method('getRequest')->willReturn($request);

        if ($response) {
            $track->expects($this->any())->method('hasResponse')->willReturn(true);
            $track->expects($this->any())->method('getResponse')->willReturn($response);
        } else {
            $track->expects($this->any())->method('hasResponse')->willReturn(false);
        }

        if ($exception) {
            $track->expects($this->any())->method('hasException')->willReturn(true);
            $track->expects($this->any())->method('getException')->willReturn($exception);
        } else {
            $track->expects($this->any())->method('hasException')->willReturn(false);
        }

        return $track;
    }
}
