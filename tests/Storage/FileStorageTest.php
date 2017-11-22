<?php

namespace Http\Client\Plugin\Vcr\Storage;

use Http\Client\Plugin\Vcr\Exception\Storage;
use Http\Client\Plugin\Vcr\Exception\NotFound;
use Http\Client\Plugin\Vcr\Tape;
use Http\Client\Plugin\Vcr\VcrTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers \Http\Client\Plugin\Vcr\Storage\FileStorage
 */
class FileStorageTest extends VcrTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var FileStorage
     */
    private $storage;

    protected function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup();
        $this->storage = new FileStorage($this->root->url());
    }

    public function testWithNonExistingDir()
    {
        $dir = vfsStream::url('non_existing');

        $this->expectException(Storage::class);
        $this->expectExceptionMessage($dir.' does not exist');

        new FileStorage($dir);
    }

    public function testWithFileInsteadOfDir()
    {
        vfsStream::newFile('file')->at($this->root)->setContent('any');

        $dir = $this->root->getChild('file')->url();

        $this->expectException(Storage::class);
        $this->expectExceptionMessage($dir.' is not a directory');

        new FileStorage($dir);
    }

    public function testWithNonWritableDir()
    {
        $this->root->chmod(0444);

        $dir = $this->root->url();

        $this->expectException(Storage::class);
        $this->expectExceptionMessage($dir.' is not writable');

        new FileStorage($dir);
    }

    public function testFetch()
    {
        $tape = $this->createTape('my_tape');
        vfsStream::newFile($tape->getName())->at($this->root)->setContent(serialize($tape));

        $this->assertInstanceOf(Tape::class, $this->storage->fetch($tape->getName()));
    }

    public function testFetchNonExisting()
    {
        $this->expectException(NotFound::class);
        $this->storage->fetch('non_existing');
    }

    public function testStore()
    {
        $tape = $this->createTape('my_tape');

        $this->storage->store($tape);

        $this->root->hasChild($tape->getName());
    }
}
