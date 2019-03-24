<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\NamingStrategy;

use Psr\Http\Message\RequestInterface;

/**
 * In charge of giving a deterministic name to a request.
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
interface NamingStrategyInterface
{
    public function name(RequestInterface $request): string;
}
