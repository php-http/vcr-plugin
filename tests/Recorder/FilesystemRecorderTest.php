<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Tests\Recorder;

use GuzzleHttp\Psr7\Response;
use Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Tests\FilesystemTestCase;

/**
 * @internal
 */
class FilesystemRecorderTest extends FilesystemTestCase
{
    /**
     * @var FilesystemRecorder
     */
    private $recorder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recorder = new FilesystemRecorder($this->workspace, $this->filesystem);
    }

    public function testReplay(): void
    {
        /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())
            ->method('debug')
            ->with('[VCR-PLUGIN][FilesystemRecorder] Unable to replay {filename}', ['filename' => "$this->workspace/file_not_found.txt"]);

        $this->recorder->setLogger($logger);

        $this->assertNull($this->recorder->replay('file_not_found'), 'No response should be returned');
    }

    public function testRecord(): void
    {
        $original = new Response(200, ['X-Foo' => 'Bar'], 'The content');

        $this->recorder->record('my_awesome_response', $original);

        $replayed = $this->recorder->replay('my_awesome_response');

        $this->assertNotNull($replayed, 'Response should not be null');

        $this->assertSame($original->getStatusCode(), $replayed->getStatusCode());
        $this->assertSame($original->getHeaders(), $replayed->getHeaders());
        $this->assertSame((string) $original->getBody(), (string) $replayed->getBody());
    }
}
