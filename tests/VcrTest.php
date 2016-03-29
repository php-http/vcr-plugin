<?php

namespace Http\Client\Plugin\Vcr;

use Http\Client\Plugin\Vcr\Exception\InvalidState;
use Http\Client\Plugin\Vcr\Exception\NotFound;

/**
 * @covers Http\Client\Plugin\Vcr\Vcr
 */
class VcrTest extends VcrTestCase
{
    /**
     * @var Vcr
     */
    private $vcr;

    /**
     * @var Tape|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tape;

    /**
     * @var Storage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    protected function setUp()
    {
        $this->storage = $this->getMock(Storage::class);
        $this->tape = $this->getMockBuilder(Tape::class)->disableOriginalConstructor()->getMock();

        $this->vcr = Vcr::createWithStorage($this->storage);
    }

    public function testTurnOnAndTurnOff()
    {
        $this->assertFalse($this->vcr->isTurnedOn());

        $this->vcr->turnOn();
        $this->assertTrue($this->vcr->isTurnedOn());

        $this->vcr->turnOff();
        $this->assertFalse($this->vcr->isTurnedOn());
    }

    public function testStartRecordingWithTurnedOffVcr()
    {
        $this->expectException(InvalidState::class);

        $this->vcr->startRecording();
    }

    public function testStartRecordingWithoutInsertedTape()
    {
        $this->vcr->turnOn();
        $this->expectException(InvalidState::class);

        $this->vcr->startRecording();
    }

    public function testStartRecording()
    {
        $this->vcr->turnOn();
        $this->vcr->insert($this->tape);

        $this->vcr->startRecording();
        $this->assertTrue($this->vcr->isRecording());
    }

    public function testStopRecording()
    {
        $this->vcr->turnOn();
        $this->vcr->insert($this->tape);

        $this->vcr->startRecording();
        $this->assertTrue($this->vcr->isRecording());

        $this->vcr->stopRecording();
        $this->assertFalse($this->vcr->isRecording());
    }

    public function testGetTape()
    {
        $this->vcr->insert($this->tape);

        $this->assertSame($this->tape, $this->vcr->getTape());
    }

    public function testGetNonExistingTape()
    {
        $this->expectException(InvalidState::class);
        $this->vcr->getTape();
    }

    public function testInsertTapeWithFilledDeck()
    {
        $this->vcr->insert($this->tape);

        $this->expectException(InvalidState::class);
        $this->vcr->insert($this->tape);
    }

    public function testInsertTapeWithGivenName()
    {
        $this->storage->expects($this->once())->method('fetch')->with('my_tape')->willReturn($this->tape);

        $this->vcr->insert('my_tape');

        $this->assertSame($this->tape, $this->vcr->getTape());
    }

    public function testInsertNewTapeWithGivenName()
    {
        $this->storage->expects($this->once())->method('fetch')->with('my_tape')->willThrowException(new NotFound());

        $this->vcr->insert('my_tape');

        $this->assertInstanceOf(Tape::class, $this->vcr->getTape());
    }

    public function testEject()
    {
        $this->vcr->insert($this->tape);
        $this->vcr->eject();

        $this->assertFalse($this->vcr->hasTape());
    }
}
