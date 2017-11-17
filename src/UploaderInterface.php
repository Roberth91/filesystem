<?php

namespace Filesystem\FileUpload;

/**
 * Class Uploader
 */
interface UploaderInterface
{
    public function upload(\SplFileInfo $localFile, $remoteFileName);

    public function download($remoteFileName, \SplFileInfo $localFile);

    public function write($content, $remoteFileName);

    public function read($remoteFileName);

    public function fileSize($remoteFileName);

    public function listDirectory($filter = '');

    public function makeDirectory($directory);
}
