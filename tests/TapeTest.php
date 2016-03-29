<?php

namespace Http\Client\Plugin\Vcr;

use GuzzleHttp\Psr7\Request;
use Http\Client\Plugin\Vcr\Exception\NotFound;

/**
 * @covers Http\Client\Plugin\Vcr\Tape
 */
class TapeTest extends VcrTestCase
{
    /**
     * @var Tape
     */
    private $tape;

    private $track;

    private $request;

    protected function setUp()
    {
        $this->request = new Request('GET', 'http://example.com');
        $this->track = $this->createTrack($this->request);

        $this->tape = new Tape('my_tape');
    }

    public function testCreateTape()
    {
        $this->assertInstanceOf(Tape::class, Tape::create('my_tape'));
    }

    public function testGetName()
    {
        $this->assertEquals('my_tape', $this->tape->getName());
    }

    public function testAddTrack()
    {
        $this->tape->addTrack($this->track);

        $this->assertSame($this->track, $this->tape->findTrackByRequest($this->request));
    }

    public function testFindNonExistingTrack()
    {
        $this->expectException(NotFound::class);
        $this->tape->findTrackByRequest(new Request('GET', 'http://nonexisting.tld'));
    }
}
