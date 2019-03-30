<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Recorder;

use Psr\Http\Message\ResponseInterface;

/**
 * Store responses in memory.
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
final class InMemoryRecorder implements PlayerInterface, RecorderInterface
{
    /**
     * @var ResponseInterface[]
     */
    private $responses = [];

    public function replay(string $name): ?ResponseInterface
    {
        return $this->responses[$name] ?? null;
    }

    public function record(string $name, ResponseInterface $response): void
    {
        $this->responses[$name] = $response;
    }

    public function clear(): void
    {
        $this->responses = [];
    }
}
