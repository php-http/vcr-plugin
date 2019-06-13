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
    /**
     * @param string $name
     *
     * @return ResponseInterface|null
     */
    public function replay(string $name);
}
