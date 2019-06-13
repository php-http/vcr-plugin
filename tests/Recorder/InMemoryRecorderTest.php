<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Tests\Recorder;

use GuzzleHttp\Psr7\Response;
use Http\Client\Plugin\Vcr\Recorder\InMemoryRecorder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class InMemoryRecorderTest extends TestCase
{
    /**
     * @var InMemoryRecorder
     */
    private $recorder;

    protected function setUp()
    {
        $this->recorder = new InMemoryRecorder();
    }

    public function testClear()
    {
        $this->recorder->record('foo', new Response());
        $this->recorder->clear();

        $this->assertNull($this->recorder->replay('foo'), 'Should not return a response');
    }

    public function testReplay()
    {
        $response = new Response();

        $this->recorder->record('foo', $response);

        $this->assertNull($this->recorder->replay('bar'), 'Should not return a response');
        $this->assertSame($response, $this->recorder->replay('foo'));
    }
}
