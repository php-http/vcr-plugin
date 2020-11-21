<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Recorder;

use GuzzleHttp\Psr7;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Stores responses using the `guzzlehttp/psr7` library to serialize and deserialize the response.
 * Target directory should be part of your VCS.
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
final class FilesystemRecorder implements RecorderInterface, PlayerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $filters;

    public function __construct(string $directory, ?Filesystem $filesystem = null, array $filters = [])
    {
        $this->filesystem = $filesystem ?? new Filesystem();

        if (!$this->filesystem->exists($directory)) {
            try {
                $this->filesystem->mkdir($directory);
            } catch (IOException $e) {
                throw new \InvalidArgumentException("Unable to create directory \"$directory\"/: {$e->getMessage()}", $e->getCode(), $e);
            }
        }

        $this->directory = realpath($directory).\DIRECTORY_SEPARATOR;
        $this->filters = $filters;
    }

    public function replay(string $name): ?ResponseInterface
    {
        $filename = "{$this->directory}$name.txt";
        $context = compact('filename');

        if (!$this->filesystem->exists($filename)) {
            $this->log('Unable to replay {filename}', $context);

            return null;
        }

        $this->log('Response replayed from {filename}', $context);

        return Psr7\parse_response(file_get_contents($filename));
    }

    public function record(string $name, ResponseInterface $response): void
    {
        $filename = "{$this->directory}$name.txt";
        $context = compact('name', 'filename');

        $content = preg_replace(array_keys($this->filters), array_values($this->filters), Psr7\str($response));
        $this->filesystem->dumpFile($filename, $content);

        $this->log('Response for {name} stored into {filename}', $context);
    }

    private function log(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->debug("[VCR-PLUGIN][FilesystemRecorder] $message", $context);
        }
    }
}
