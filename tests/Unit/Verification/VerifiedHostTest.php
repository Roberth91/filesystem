<?php

namespace tests\Unit\Verification;

use Filesystem\FileUpload\Verification\VerifiedHost;

class VerifiedHostTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @param $host
	 * @dataProvider providerHost
	 */
	public function testItValidatesHostname($host)
	{
		$this->assertEquals($host, (new VerifiedHost($host))->getHostname());
	}

	/**
	 * @param $host
	 * @dataProvider providerFailHost
	 */
	public function testItInvalidatesBadHostname($host)
	{
		$this->expectException("InvalidArgumentException");

		$this->expectExceptionMessage("The hostname: '$host' was invalid. Please check the configuration.");

		(new VerifiedHost($host))->getHostname();
	}

	public function providerHost()
	{
		return [
			["ftp.example.com"],
			["example.com"],
			["sftp-end.example.com"],
			["sftp-end.example-one.com"],
			["ftp.123example.com"],
			["123example.com"],
			["123.sub.example.com"],
			["123.123.123.123"],
			["artemis-ftg"],
			["accmgrft"],
		];
	}

	public function providerFailHost()
	{
		return [
			["sftp://sftp.example.com"],
			["sft@p.ex!ample.com"],
		];
	}

}