<?php

namespace Filesystem\FileUpload\Driver;

use Filesystem\FileUpload\Verification\VerifiedHost;

/**
 * Class FtpDriver
 * @package Filesystem\FileUpload\Driver
 */
class FtpDriver implements  DriverInterface
{
	private $connection;

	/**
	 * @var VerifiedHost
	 */
	private $host;

	private $port = 21;

	private $username;

	private $password;

	private $root;

	private $ssl = false;

	private $passive = true;

	private $timeout = 90;

	private $allowedParams = ['host', 'port', 'username', 'password', 'root', 'ssl', 'passive', 'timeout'];

	public function __construct(array $config)
	{
		foreach ($this->allowedParams as $param) {
			if (isset($config[$param])) {
				$this->{$param} = $config[$param];
			}
		}

		$this->host = (new VerifiedHost($this->host))->getHostname();

		$this->connect();
	}

	public function isConnected()
	{
		return $this->connection !== null;
	}

	public function connect()
	{
		$this->connection = $this->ssl ? ftp_ssl_connect($this->host, $this->port, $this->timeout) : ftp_connect($this->host, $this->port, $this->timeout);

		if ($this->connection === false) {
			throw new \Exception("Could not connect to $this->host:$this->port");
		}

		if ($this->username) {
			ftp_login($this->connection, $this->username, $this->password);
		}

		if ($this->passive) {
			ftp_pasv($this->connection, true);
		}

		if ($this->root) {
			ftp_chdir($this->connection, $this->root);
		}
	}

	public function put($localFile, $remoteFile)
	{
		ftp_put($this->connection, $remoteFile, $localFile, FTP_BINARY);
	}

	public function putDirectory($directory, $targetDirectory)
	{
		throw new \Exception("Feature not implemented");
	}

	public function get($remoteFile, \SplFileInfo $localFile)
	{
		ftp_get($this->connection, $localFile, $remoteFile, FTP_BINARY);
	}

	public function read($remoteFile)
	{
		$file = fopen("php://memory", "r+");

		ftp_fget($this->connection, $file, $remoteFile, FTP_BINARY);

		$len = ftell($file);

		fseek($file, 0);

		return fread($file, $len);
	}

	public function write($remoteFile, $content)
	{
		$file = fopen("php://memory", "r+");

		fwrite($file, $content);

		rewind($file);

		ftp_fput($this->connection, $remoteFile, $file, FTP_BINARY);

		return $this;
	}

	public function listContents($directory = '')
	{
		$list = ftp_nlist($this->connection, $directory);

		return $list === false ? array() : $list;
	}

	public function getSize($remoteFile)
	{
		return ftp_size($this->connection, $remoteFile);
	}

	public function makeDirectory($directory)
	{
		$pathParts = explode('/', $directory);

		$dirs = array();

		while ($pop = array_shift($pathParts)) {
			$dirs[] = $pop;

			$dir = implode('/', $dirs);

			@ftp_mkdir($this->connection, $dir);
		}

		return $this;
	}

	public function disconnect()
	{
		if ($this->connection) {
			ftp_close($this->connection);
		}

		$this->connection = null;
	}

}