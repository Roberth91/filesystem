<?php

namespace Filesystem\FileUpload\Verification;

/**
 * Class VerifiedHost
 * @package Filesystem\FileUpload\Verification
 */
class VerifiedHost
{
	/**
	 * @var $host
	 */
	private $host;

	/**
	 * VerifiedHost constructor.
	 *
	 * @param $host
	 */
	public function __construct($host)
	{
		$this->host = $host;

		$this->validateHost();
	}

	private function validateHost()
	{
		if(!$this->host || !preg_match("/^[a-zA-Z0-9._-]+$/", $this->host))
		{
			throw new \InvalidArgumentException("The hostname: '$this->host' was invalid. Please check the configuration.");
		}
	}

	/**
	 * @return mixed
	 */
	public function getHostname()
	{
		return $this->host;
	}
}