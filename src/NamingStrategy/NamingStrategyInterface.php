<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\NamingStrategy;

use Psr\Http\Message\RequestInterface;

/**
 * Provides a deterministic and unique identifier for a request. The identifier must be safe to use with a filesystem.
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
interface NamingStrategyInterface
{
    public function name(RequestInterface $request): string;
}
