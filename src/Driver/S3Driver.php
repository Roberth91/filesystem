<?php

namespace Filesystem\FileUpload\Driver;

use Aws\S3\S3Client;

/**
 * Class S3Driver
 * @package Filesystem\FileUpload\Driver
 */
class S3Driver implements  DriverInterface
{
	const S3_VERSION = 'latest';

	const AES_ENCRYPTION = 'AES256';

	const ACL = 'private';

	/**
	 * @var S3Client
	 */
	private $connection;

	private $bucket;

	private $prefix;

	private $key;

	private $secret;

	private $region;

	private $encrypt;

	private $timeout = 0;

	private $connect_timeout = 90;

	private $allowedParams = ['bucket', 'prefix', 'key', 'secret', 'region', 'timeout', 'connect_timeout', 'encrypt'];

	public function __construct(array $config)
	{
		foreach($this->allowedParams as $param)
		{
			if(isset($config[$param]))
			{
				$this->{$param} = $config[$param];
			}
		}

		if($this->prefix)
		{
			$this->prefix = rtrim($this->prefix, '/') . '/';
		}

		$this->connect();
	}

	public function connect()
	{
		$timeout = [
			'connect_timeout' 	=> $this->connect_timeout,
		];

		if($this->timeout)
		{
			$timeout['timeout'] = $this->timeout;
		}

		$this->connection = new S3Client([
			'version' => self::S3_VERSION,
			'region' => $this->region,
			'credentials' => ['key' =>  $this->key, 'secret' => $this->secret],
			'http'    => $timeout
		]);
	}

	/**
	 * @param $localFile
	 * @param $remoteFile
	 */
	public function put($localFile, $remoteFile)
	{
		$this->connection->upload($this->bucket, $this->prefix . $remoteFile, new \SplFileObject($localFile, 'r'), self::ACL, $this->getServerSideEncryptionOpts());
	}

	/**
	 * @param $directory
	 * @param $bucketExtension
	 */
	public function putDirectory($directory, $bucketExtension)
	{
		$this->connection->uploadDirectory($directory, $this->bucket, $this->prefix.$bucketExtension, $this->getServerSideEncryptionOpts());
	}

	public function get($remoteFile, \SplFileInfo $localFile)
	{
		$this->connection->getObject(['Bucket' => $this->bucket, 'Key' =>  $this->prefix . $remoteFile, 'SaveAs' => $localFile->getPathname()]);
	}

	public function read($remoteFile)
	{
		$result = $this->connection->getObject(['Bucket' => $this->bucket, 'Key' =>  $this->prefix . $remoteFile]);

		return $result['Body'];
	}

	/**
	 * @param $remoteFile
	 * @param $content
	 * @param bool $encryption
	 */
	public function write($remoteFile, $content, $encryption = false)
	{
		$this->connection->upload($this->bucket, $this->prefix . $remoteFile, $content, self::ACL, $this->getServerSideEncryptionOpts());
	}

	public function listContents($directory = '')
	{
		$results = $this->connection->getPaginator('ListObjects', [
			'Bucket' => $this->bucket,
			'Prefix' => $this->prefix,
			'Delimiter' => '/'
		]);

		$keys = [];

		foreach ($results as $result) {
			foreach ($result['Contents'] ?: [] as $object) {

				$key = basename($object['Key']);

				if(!$directory || fnmatch($directory, $key))
				{
					$keys[] = $key;
				}
			}
		}

		return $keys;
	}

	public function getSize($remoteFile)
	{
		$result = $this->connection->headObject(['Bucket' => $this->bucket, 'Key' =>  $this->prefix . $remoteFile]);

		return $result['ContentLength'];
	}

	public function makeDirectory($directory)
	{
		// No need to create directories on s3
//		throw new \RuntimeException("Not implemented");

		return false;
	}

	public function disconnect()
	{

	}

	private function getServerSideEncryptionOpts()
	{
		return $this->encrypt ? ['ServerSideEncryption' => self::AES_ENCRYPTION] : [];
	}

}