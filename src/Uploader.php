<?php

namespace Filesystem\FileUpload;

use Filesystem\FileUpload\Driver\DriverInterface;

/**
 * Class Uploader
 */
class Uploader implements UploaderInterface
{
    protected $adapter;

    public function __construct(DriverInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function upload(\SplFileInfo $localFile, $remoteFileName)
    {
        $this->adapter->put($localFile, $remoteFileName);
    }

    public function uploadDirectory($directory, $bucket)
    {
        $this->adapter->putDirectory($directory, $bucket);
    }

    public function download($remoteFileName, \SplFileInfo $localFile)
    {
        $this->adapter->get($remoteFileName, $localFile);
    }

    public function write($content, $remoteFileName)
    {
        $this->adapter->write($remoteFileName, $content);
    }

    public function read($remoteFileName)
    {
        return $this->adapter->read($remoteFileName);
    }

    public function fileSize($remoteFileName)
    {
        return $this->adapter->getSize($remoteFileName);
    }

    public function makeDirectory($directory)
    {
        $this->adapter->makeDirectory($directory);
    }

    public function listDirectory($filter = '')
    {
        $directory = $this->adapter->listContents($filter);

        return $directory;
    }

    public function connect()
    {
        $this->adapter->connect();
    }

    public function disconnect()
    {
        $this->adapter->disconnect();
    }
}
