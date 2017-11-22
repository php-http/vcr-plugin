<?php

namespace Http\Client\Plugin\Vcr;

use Http\Client\Plugin\Vcr\Exception\NotFound;

interface Storage
{
    /**
     * Stores the given tape.
     *
     * @param Tape $tape
     */
    public function store(Tape $tape);

    /**
     * Returns a tape with the given name.
     *
     * @param string $name
     *
     * @throws NotFound if the requested tape has not been found
     *
     * @return Tape
     */
    public function fetch($name);
}
