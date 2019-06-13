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

    /**
     * @param string $name
     *
     * @return ResponseInterface|null
     */
    public function replay(string $name)
    {
        return $this->responses[$name] ?? null;
    }

    /**
     * @param string            $name
     * @param ResponseInterface $response
     */
    public function record(string $name, ResponseInterface $response)
    {
        $this->responses[$name] = $response;
    }

    public function clear()
    {
        $this->responses = [];
    }
}
