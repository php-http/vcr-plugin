<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Recorder;

use Psr\Http\Message\ResponseInterface;

/**
 * In change of retrieving a previously recorded response.
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
interface PlayerInterface
{
    public function replay(string $name): ?ResponseInterface;
}
