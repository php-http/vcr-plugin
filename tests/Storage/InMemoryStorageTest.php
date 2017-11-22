<?php

namespace Http\Client\Plugin\Vcr\Storage;

use Http\Client\Plugin\Vcr\Exception\NotFound;
use Http\Client\Plugin\Vcr\VcrTestCase;

/**
 * @covers \Http\Client\Plugin\Vcr\Storage\InMemoryStorage
 */
class InMemoryStorageTest extends VcrTestCase
{
    /**
     * @var InMemoryStorage
     */
    protected $storage;

    protected function setUp()
    {
        parent::setUp();
        $this->storage = new InMemoryStorage();
    }

    public function testCreateWithExistingTapes()
    {
        $first = $this->createTape('first');
        $second = $this->createTape('second');

        $storage = new InMemoryStorage([$first, $second]);

        $this->assertSame($first, $storage->fetch($first->getName()));
        $this->assertSame($second, $storage->fetch($second->getName()));
    }

    public function testStore()
    {
        $tape = $this->createTape('tape');

        $this->storage->store($tape);
        $this->assertSame($tape, $this->storage->fetch($tape->getName()));
    }

    public function testFetchNonExisting()
    {
        $this->expectException(NotFound::class);
        $this->storage->fetch('non_existing');
    }
}
