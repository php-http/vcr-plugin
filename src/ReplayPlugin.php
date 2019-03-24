<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr;

use Http\Client\Common\Plugin;
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

    public function __construct(NamingStrategyInterface $namingStrategy, PlayerInterface $player)
    {
        $this->namingStrategy = $namingStrategy;
        $this->player = $player;
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

        return $next($request);
    }
}
