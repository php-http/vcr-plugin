<?php

namespace Http\Client\Plugin\Vcr;

use Psr\Http\Message\RequestInterface;

class Tape
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Track[]
     */
    private $tracks;

    public function __construct($name)
    {
        $this->name = $name;
        $this->tracks = [];
    }

    public static function create($name)
    {
        return new self($name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function addTrack(Track $track)
    {
        $this->tracks[] = $track;
    }

    /**
     * @param RequestInterface $request
     *
     * @throws Exception\NotFound
     *
     * @return Track
     */
    public function findTrackByRequest(RequestInterface $request)
    {
        $requestMethod = $request->getMethod();
        $requestUriString = (string) $request->getUri();

        foreach ($this->tracks as $track) {
            $trackRequest = $track->getRequest();
            if ($trackRequest->getMethod() === $requestMethod && (string) $trackRequest->getUri() === $requestUriString) {
                return $track;
            }
        }

        throw new Exception\NotFound(sprintf('No track found for %s Request to %s.', $requestMethod, $requestUriString));
    }
}
