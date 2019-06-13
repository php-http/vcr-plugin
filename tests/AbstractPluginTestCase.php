<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Tests;

use Http\Client\Common\Plugin;
use Http\Client\Plugin\Vcr\NamingStrategy\NamingStrategyInterface;
use Http\Client\Plugin\Vcr\Recorder\InMemoryRecorder;
use Http\Promise\Promise;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

abstract class AbstractPluginTestCase extends TestCase
{
    /**
     * @var NamingStrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $namingStrategy;

    /**
     * @var InMemoryRecorder
     */
    protected $recorder;

    /**
     * @var Plugin
     */
    protected $plugin;

    protected function setUp()
    {
        $pluginClass = $this->getPluginClass();
        $this->namingStrategy = $this->createMock(NamingStrategyInterface::class);
        $this->recorder = new InMemoryRecorder();
        $this->plugin = new $pluginClass($this->namingStrategy, $this->recorder);
    }

    protected function getRequest(): RequestInterface
    {
        return $this->createMock(RequestInterface::class);
    }

    protected function failCallback(): callable
    {
        return function (): Promise {
            $this->fail('Never called');
        };
    }

    abstract protected function getPluginClass(): string;
}
