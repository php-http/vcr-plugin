<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr;

use Http\Client\Common\Plugin;
use Http\Client\Exception\RequestException;
use Http\Client\Plugin\Vcr\NamingStrategy\NamingStrategyInterface;
use Http\Client\Plugin\Vcr\Recorder\PlayerInterface;
use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

final class ReplayPlugin implements Plugin
{
    const HEADER_NAME = 'X-VCR-REPLAYED';

    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * @var PlayerInterface
     */
    private $player;

    /**
     * Throw an exception if not able to replay a request.
     *
     * @var bool
     */
    private $throw;

    public function __construct(NamingStrategyInterface $namingStrategy, PlayerInterface $player, bool $throw = true)
    {
        $this->namingStrategy = $namingStrategy;
        $this->player = $player;
        $this->throw = $throw;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $name = $this->namingStrategy->name($request);

        if ($response = $this->player->replay($name)) {
            return new FulfilledPromise($response->withAddedHeader(static::HEADER_NAME, $name));
        }

        if ($this->throw) {
            throw new RequestException("Unable to find a response to replay request \"$name\".", $request);
        }

        return $next($request);
    }

    /**
     * Whenever the plugin should throw an exception when not able to replay a request.
     *
     * @return $this
     */
    public function throwOnNotFound(bool $throw)
    {
        $this->throw = $throw;

        return $this;
    }
}
