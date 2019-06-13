<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\Recorder;

use Psr\Http\Message\ResponseInterface;

/**
 * In charge of recording a response to a support (Filesystem, database, ...).
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
interface RecorderInterface
{
    /**
     * @param string            $name
     * @param ResponseInterface $response
     */
    public function record(string $name, ResponseInterface $response);
}
