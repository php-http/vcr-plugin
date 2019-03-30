<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr;

use Http\Client\Common\Plugin;
use Http\Client\Plugin\Vcr\NamingStrategy\NamingStrategyInterface;
use Http\Client\Plugin\Vcr\Recorder\RecorderInterface;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RecordPlugin implements Plugin
{
    const HEADER_NAME = 'X-VCR-RECORD';

    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * @var RecorderInterface
     */
    private $recorder;

    public function __construct(NamingStrategyInterface $namingStrategy, RecorderInterface $recorder)
    {
        $this->namingStrategy = $namingStrategy;
        $this->recorder = $recorder;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $name = $this->namingStrategy->name($request);

        return $next($request)->then(function (ResponseInterface $response) use ($name) {
            if (!$response->hasHeader(ReplayPlugin::HEADER_NAME)) {
                $this->recorder->record($name, $response);
                $response = $response->withAddedHeader(static::HEADER_NAME, $name);
            }

            return $response;
        });
    }
}
