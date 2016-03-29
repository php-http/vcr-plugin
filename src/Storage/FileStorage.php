<?php

namespace Http\Client\Plugin\Vcr\Storage;

use Http\Client\Plugin\Vcr\Exception;
use Http\Client\Plugin\Vcr\Storage;
use Http\Client\Plugin\Vcr\Tape;

class FileStorage implements Storage
{
    private $dir;

    public function __construct($dir)
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        if (!file_exists($dir)) {
            throw new Exception\Storage(sprintf('%s does not exist.', $dir));
        }

        if (!is_dir($dir)) {
            throw new Exception\Storage(sprintf('%s is not a directory.', $dir));
        }

        if (!is_writable($dir)) {
            throw new Exception\Storage(sprintf('%s is not writable', $dir));
        }

        $this->dir = $dir;
    }

    public function store(Tape $tape)
    {
        $filePath = $this->getFilePathForName($tape->getName());

        file_put_contents($filePath, serialize($tape));
    }

    public function fetch($name)
    {
        $filePath = $this->getFilePathForName($name);

        if (!file_exists($filePath)) {
            throw new Exception\NotFound(sprintf('Tape with name "%s" not found.', $name));
        }

        return unserialize(file_get_contents($filePath));
    }

    private function getFilePathForName($name)
    {
        return $this->dir.DIRECTORY_SEPARATOR.$name;
    }
}
