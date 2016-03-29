<?php

namespace Http\Client\Plugin\Vcr\Storage;

use Http\Client\Plugin\Vcr\Exception;
use Http\Client\Plugin\Vcr\Storage;
use Http\Client\Plugin\Vcr\Tape;

class InMemoryStorage implements Storage
{
    /**
     * @var Tape[]
     */
    private $tapes = [];

    public function __construct(array $tapes = [])
    {
        $this->tapes = [];

        foreach ($tapes as $tape) {
            $this->store($tape);
        }
    }

    public function store(Tape $tape)
    {
        $key = $this->createKey($tape->getName());

        $this->tapes[$key] = $tape;
    }

    public function fetch($name)
    {
        $key = $this->createKey($name);

        if (!array_key_exists($key, $this->tapes)) {
            throw new Exception\NotFound(sprintf('Tape with name "%s" not found.', $name));
        }

        return $this->tapes[$key];
    }

    private function createKey($name)
    {
        return md5($name);
    }
}
