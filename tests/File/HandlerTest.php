<?php

namespace ExodusTests;

use Exodus\File\Handler as FileHandler;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Tests for Exodus\File\Handler.
 */
class HandlerTest extends TestCase
{
    /**
     * The virtual file system to test against.
     */
    protected $file_system;

    /**
     * Sets up a virtual file system.
     */
    public function setUp()
    {
        $directory = [
            'tmp' => [
                'file1.txt' => 'These are the first file contents.',
                'file2.txt' => 'These are the second file contents.',
                'tmp2' => [
                    'file3.txt' => 'These are the third file contents.'
                ]
            ]
        ];

        $this->file_system = vfsStream::setup('root', 444, $directory);
    }

    /**
     * @group file.handler
     */
    public function testFileGetContentsReturnsFileContents()
    {
        $file_handler = new FileHandler();

        $this->assertEquals(
            'These are the first file contents.', 
            $file_handler->fileGetContents(vfsStream::url('root/tmp/file1.txt'))
        );
    }

    /**
     * @group file.handler
     */
    public function testScanDirReturnsAllNodesInTheDirectory()
    {
        $file_handler = new FileHandler();

        $this->assertEquals(
            ['.', '..', 'file1.txt', 'file2.txt', 'tmp2'],
            $file_handler->scanDir(vfsStream::url('root/tmp'))
        );
    }

    /**
     * @group file.handler
     */
    public function testIsFileReturnsTrueWhenDirectoryNodeIsAFile()
    {
        $file_handler = new FileHandler();

        $this->assertTrue($file_handler->isFile(vfsStream::url('root/tmp/file1.txt')));
    }

    /**
     * @group file.handler
     */
    public function testIsFileReturnsFalseWhenDirectoryNodeIsADirectory()
    {
        $file_handler = new FileHandler();

        $this->assertFalse($file_handler->isFile(vfsStream::url('root/tmp/tmp2')));
    }

    /**
     * @group file.handler
     */
    public function testIsFileReturnsFalseWhenDirectoryNodeIsTwoDots()
    {
        $file_handler = new FileHandler();

        $this->assertFalse($file_handler->isFile(vfsStream::url('root/tmp/..')));
    }

    /**
     * @group file.handler
     */
    public function testIsFileReturnsFalseWhenDirectoryNodeIsOneDot()
    {
        $file_handler = new FileHandler();

        $this->assertFalse($file_handler->isFile(vfsStream::url('root/tmp/.')));
    }

    /**
     * @group file.handler
     */
    public function testCopySuccessfullyCopiesFileToAnotherLocation()
    {
        $file_handler = new FileHandler();
        $file_handler->copy(vfsStream::url('root/tmp/file1.txt'), vfsStream::url('root/tmp/tmp2') . '/file1.txt');

        $this->assertTrue($this->file_system->hasChild('root/tmp/tmp2/file1.txt'));
    }

    /**
     * @group file.handler
     */
    public function testFileExistsReturnsTrueIfFileDoesExist()
    {
        $file_handler = new FileHandler();

        $this->assertTrue($file_handler->fileExists(vfsStream::url('root/tmp/file1.txt')));
    }

    /**
     * @group file.handler
     */
    public function testFileExistsReturnsFalseIfFileDoesNotExist()
    {
        $file_handler = new FileHandler();

        $this->assertFalse($file_handler->fileExists(vfsStream::url('root/tmp/invalid.txt')));
    }
}