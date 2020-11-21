<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Tests\Recorder;

use GuzzleHttp\Psr7\Response;
use Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
class FilesystemRecorderTest extends TestCase
{
    /**
     * @var FilesystemRecorder
     */
    private $recorder;

    /**
     * @var int
     */
    private $umask;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $workspace;

    /**
     * @var TestLogger
     */
    private $logger;

    /**
     * @see https://github.com/symfony/symfony/blob/5.x/src/Symfony/Component/Filesystem/Tests/FilesystemTestCase.php
     */
    protected function setUp(): void
    {
        $this->umask = umask(0);
        $this->filesystem = new Filesystem();
        $this->workspace = sys_get_temp_dir().'/'.microtime(true).'.'.mt_rand();
        mkdir($this->workspace, 0777, true);
        $this->workspace = realpath($this->workspace);
        $this->logger = new TestLogger();

        $this->recorder = new FilesystemRecorder($this->workspace, $this->filesystem);
        $this->recorder->setLogger($this->logger);
    }

    public function testReplay(): void
    {
        $this->assertNull($this->recorder->replay('file_not_found'), 'No response should be returned');
        $this->assertTrue(
            $this->logger->hasDebug('[VCR-PLUGIN][FilesystemRecorder] Unable to replay {filename}'),
            'Cache miss should be logged'
        );
    }

    public function testRecord(): void
    {
        $original = new Response(200, ['X-Foo' => 'Bar'], 'The content');

        $this->recorder->record('my_awesome_response', $original);
        $this->assertFileExists(sprintf('%s%smy_awesome_response.txt', $this->workspace, \DIRECTORY_SEPARATOR));

        $replayed = (new FilesystemRecorder($this->workspace))->replay('my_awesome_response');

        $this->assertNotNull($replayed, 'Response should not be null');

        $this->assertSame($original->getStatusCode(), $replayed->getStatusCode());
        $this->assertSame($original->getHeaders(), $replayed->getHeaders());
        $this->assertSame((string) $original->getBody(), (string) $replayed->getBody());
    }

    protected function tearDown(): void
    {
        if (!empty($this->longPathNamesWindows)) {
            foreach ($this->longPathNamesWindows as $path) {
                exec('DEL '.$path);
            }
            $this->longPathNamesWindows = [];
        }

        $this->filesystem->remove($this->workspace);
        umask($this->umask);
    }

    public function testRecordWithFilter(): void
    {
        $original = new Response(200, ['X-Foo' => 'Bar', 'X-Bar' => 'private-token-065a1bb33f000032ab'], 'The content');

        $recorder = new FilesystemRecorder($this->workspace, $this->filesystem, [
            '!private-token-[0-9a-z]+!' => 'private-token-xxxx',
            '!The content!' => 'The big content',
        ]);
        $recorder->record('my_awesome_response', $original);

        $this->assertFileExists(sprintf('%s%smy_awesome_response.txt', $this->workspace, \DIRECTORY_SEPARATOR));

        $replayed = (new FilesystemRecorder($this->workspace))->replay('my_awesome_response');

        $this->assertNotNull($replayed, 'Response should not be null');

        $this->assertSame($original->getStatusCode(), $replayed->getStatusCode());
        $expectedHeaders = $original->getHeaders();
        $expectedHeaders['X-Bar'] = ['private-token-xxxx'];
        $this->assertSame($expectedHeaders, $replayed->getHeaders());
        $this->assertSame('The big content', (string) $replayed->getBody());
    }
}
