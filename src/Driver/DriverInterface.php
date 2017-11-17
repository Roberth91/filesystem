<?php

namespace Filesystem\FileUpload\Driver;

/**
 * Interface DriverInterface
 * @package Filesystem\FileUpload\Driver
 */
interface DriverInterface
{
	public function put($localFile, $remoteFile);

	public function putDirectory($directory, $bucketExtension);

	public function get($remoteFile, \SplFileInfo $localFile);

	public function read($remoteFile);

	public function write($remoteFile, $content);

	public function listContents($directory = '');

	public function getSize($remoteFile);

	public function makeDirectory($directory);

	public function disconnect();
	
	public function connect();
}