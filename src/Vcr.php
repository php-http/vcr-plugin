<?php

namespace Http\Client\Plugin\Vcr;

use Http\Client\Plugin\Vcr\Exception\InvalidState;
use Http\Client\Plugin\Vcr\Exception\NotFound;
use Http\Client\Plugin\Vcr\Storage\InMemoryStorage;

class Vcr
{
    /**
     * @var bool
     */
    private $isTurnedOn;

    /**
     * @var bool
     */
    private $isRecording;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * The currently inserted tape.
     *
     * @var Tape
     */
    private $tape;

    public function __construct(Storage $storage = null)
    {
        $this->storage = $storage ?: new InMemoryStorage();

        $this->isTurnedOn = false;
        $this->isRecording = false;
    }

    public static function createWithStorage(Storage $storage)
    {
        return new self($storage);
    }

    public function turnOn()
    {
        $this->isTurnedOn = true;
    }

    public function turnOff()
    {
        $this->isTurnedOn = false;
    }

    public function isTurnedOn()
    {
        return $this->isTurnedOn;
    }

    /**
     * Starts recording.
     *
     * @throws InvalidState if no tape has been inserted.
     */
    public function startRecording()
    {
        if (!$this->isTurnedOn()) {
            throw new InvalidState('Please turn me on first.');
        }

        if (!$this->hasTape()) {
            throw new InvalidState('Please insert a tape first.');
        }

        $this->isRecording = true;
    }

    /**
     * Stops recording.
     */
    public function stopRecording()
    {
        $this->isRecording = false;
    }

    /**
     * Returns whether the Vcr is currently recording or not.
     *
     * @return bool
     */
    public function isRecording()
    {
        return $this->isRecording;
    }

    /**
     * Returns whether a tape is currently inserted or not.
     *
     * @return bool
     */
    public function hasTape()
    {
        return (bool) $this->tape;
    }

    /**
     * Returns the currently inserted tape.
     *
     * @return Tape
     */
    public function getTape()
    {
        if (!$this->tape) {
            throw new InvalidState('Please insert a tape first.');
        }

        return $this->tape;
    }

    /**
     * Inserts the given tape.
     *
     * If an actual tape is provided, it is inserted directly.
     * If the name of a tape is provided, it will be fetched from the shelf, or created with the given name.
     *
     * @param Tape|string $tape
     *
     * @throws InvalidState If the tape could not be inserted.
     */
    public function insert($tape)
    {
        if ($this->tape) {
            throw new InvalidState(sprintf('Please eject the tape "%s" first.', $this->tape->getName()));
        }

        if (!($tape instanceof Tape)) {
            try {
                $tape = $this->storage->fetch($tape);
            } catch (NotFound $e) {
                $tape = new Tape($tape);
            }
        }

        $this->tape = $tape;
    }

    /**
     * Ejects the currently inserted tape.
     */
    public function eject()
    {
        if ($this->tape) {
            $this->storage->store($this->tape);
            $this->tape = null;
        }
    }
}
