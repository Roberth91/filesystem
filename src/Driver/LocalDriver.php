<?php

namespace Filesystem\FileUpload\Driver;

/**
 * Class LocalDriver
 * @package Filesystem\FileUpload\Driver
 */
class LocalDriver implements  DriverInterface
{
	private $root;

	private $allowedParams = ['root'];

	public function __construct(array $config)
	{
		foreach($this->allowedParams as $param)
		{
			if(isset($config[$param]))
			{
				$this->{$param} = $config[$param];
			}
		}

		if($this->root)
		{
			$this->root = rtrim($this->root, '/') . '/';
		}
	}

	public function connect()
	{

	}

	public function put($localFile, $remoteFile)
	{
		return copy($localFile, $this->root . $remoteFile);
	}

	public function putDirectory($sourceDir, $targetDir)
	{
		$contents = array_diff(glob($sourceDir), ['.', '..']);

		foreach ($contents as $file)
		{
			copy($file, $targetDir . '/'. basename($file));
		}

		return false;
	}

	public function get($remoteFile, \SplFileInfo $localFile)
	{
		return copy($this->root . $remoteFile, $localFile);
	}

	public function read($remoteFile)
	{
		return file_get_contents($this->root . $remoteFile);
	}

	public function write($remoteFile, $content)
	{
		return file_put_contents($this->root . $remoteFile, $content);
	}

	public function listContents($directory = '')
	{
		return array_map('basename', array_diff(glob($this->root . $directory), ['.', '..']));
	}

	public function getSize($remoteFile)
	{
		return filesize($this->root . $remoteFile);
	}

	public function makeDirectory($directory)
	{
		is_dir($this->root . $directory) or mkdir($this->root . $directory, 0777, true);
	}

	public function disconnect()
	{

	}

}