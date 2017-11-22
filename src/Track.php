<?php

namespace Http\Client\Plugin\Vcr;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Track
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var \Exception
     */
    private $exception;

    private function __construct(RequestInterface $request, ResponseInterface $response = null, \Exception $exception = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->exception = $exception;
    }

    public static function create(RequestInterface $request)
    {
        return new self($request);
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function hasResponse()
    {
        return (bool) $this->response;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        if ($this->response && $body = $this->response->getBody()) {
            // Rewind response body in case it has already been read
            $body->seek(0, SEEK_SET);
        }

        return $this->response;
    }

    public function hasException()
    {
        return (bool) $this->exception;
    }

    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    public function getException()
    {
        return $this->exception;
    }
}
