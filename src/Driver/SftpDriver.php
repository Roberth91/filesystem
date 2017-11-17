<?php

namespace Filesystem\FileUpload\Driver;

use Filesystem\FileUpload\Verification\VerifiedHost;
use Filesystem\Utils\Executor\SymfonyExecutor;
use Filesystem\Utils\TempFileWriter\TempFileWriter;

/**
 * Class SftpDriver
 * @package Filesystem\FileUpload\Driver
 */
class SftpDriver implements  DriverInterface
{
	private $defaultLftpOptions = array(
		"set net:persist-retries 2",
		"set net:max-retries 2",
		"set dns:max-retries 2",
		"set net:reconnect-interval-base 2",
		"set net:reconnect-interval-max 3",
		"set net:reconnect-interval-multiplier 1",
		"set sftp:auto-confirm true"
	);

	private $executor;

	private $host;

	private $port = 22;

	private $username;

	private $password;

	private $root;

	private $privateKey;

	private $timeout = 90;

	private $allowedParams = ['host', 'port', 'username', 'password', 'root', 'privateKey', 'timeout'];

	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		foreach($this->allowedParams as $param) {
			if(isset($config[$param])) {
				$this->{$param} = $config[$param];
			}
		}

		$this->host = (new VerifiedHost($this->host))->getHostname();

		$this->connect();

		$this->executor = new SymfonyExecutor();

		$this->root = trim($this->root);

		if($this->privateKey) {
			$this->privateKey = new TempFileWriter($this->privateKey);
		}
	}

	/**
	 *
	 */
	public function connect()
	{

	}

	/**
	 * @return string
	 */
	private function getRemoteDirectoryCmd()
	{
		return $this->root ? "cd $this->root &&" : '';
	}

	/**
	 * @param $options
	 * @return string
	 */
	private function concatLftpOptions($options)
	{
		return ltrim(implode("; ", $options) . ";", ";");
	}

	/**
	 * @return bool|string
	 */
	private function getSSHKeyOption()
	{
		if(!$this->privateKey) {
			return false;
		}

		// There is no requirement in the interface to set a password when using an SSH key
		// but LFTP requires that the password field be filled out. If the user hasn't supplied one
		// we just fill it out with a placeholder
		if(!$this->password) {
			$this->password = "pass";
		}

		return sprintf("set sftp:connect-program 'ssh -a -x -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i %s'", $this->privateKey->getTempFile());
	}

	/**
	 * @param $localFile
	 * @param $remoteFile
	 */
	public function put($localFile, $remoteFile)
	{
		$this->runCommand(sprintf("%s put %s -o %s", $this->getRemoteDirectoryCmd(), $localFile, $remoteFile));
	}

	public function putDirectory($directory, $targetDirectory)
	{
		throw new \Exception("Feature not implemented");
	}

	/**
	 * @param $remoteFile
	 * @param $localFile
	 */
	public function get($remoteFile, \SplFileInfo $localFile)
	{
		$this->runCommand(sprintf("set xfer:clobber on; %s get -e %s -o %s", $this->getRemoteDirectoryCmd(), $remoteFile, $localFile));
	}

	/**
	 * @param $remoteFile
	 * @return string
	 */
	public function read($remoteFile)
	{
		$localFile = new TempFileWriter();

		$this->get($remoteFile, $localFile->getTempFile());

		$contents = file_get_contents($localFile->getTempFile());

		$localFile->releaseTempFile();

		return $contents;
	}

	/**
	 * @param $remoteFile
	 * @param $content
	 */
	public function write($remoteFile, $content)
	{
		$localFile = new TempFileWriter($content);

		$this->put($localFile->getTempFile(), $remoteFile);

		$localFile->releaseTempFile();
	}

	/**
	 * @param string $directory
	 * @return array
	 */
	public function listContents($directory = '')
	{
		$op = $this->runCommand(sprintf("%s nlist %s", $this->getRemoteDirectoryCmd(), $directory));

		return array_filter(array_diff($op, ['./','../']));
	}

	/**
	 * @param $remoteFile
	 * @return int
	 */
	public function getSize($remoteFile)
	{
		$op = $this->runCommand(sprintf("%s cls -s -h %s", $this->getRemoteDirectoryCmd(), $remoteFile));

		return intval(reset($op));
	}

	/**
	 * @param $directory
	 */
	public function makeDirectory($directory)
	{
		$this->runCommand(sprintf("%s mkdir -p -f %s", $this->getRemoteDirectoryCmd(), $directory));
	}

	/**
	 * @param $command
	 * @return array
	 */
	private function runCommand($command)
	{
		$commands = array_filter(array_merge($this->defaultLftpOptions, [
			sprintf("set net:timeout %s", $this->timeout),
			$this->getSSHKeyOption(),
			$command
		]));

		$params = [
			sprintf('%s,%s',$this->username,$this->password),
			sprintf('%s', $this->concatLftpOptions($commands)),
			sprintf('%s:%s', $this->host, $this->port),
		];

		$response = $this->exec('lftp -u %s -e %s sftp://%s', $params);

		return $response;
	}

	public function disconnect()
	{

	}

	/**
	 * @param       $command
	 * @param array $params
	 * @return array
	 */
	protected function exec($command, array $params = array())
	{
		$params = array_map('escapeshellarg', $params);

		$cmd = vsprintf($command, $params);

		return $this->executor->execute($cmd);
	}

}
