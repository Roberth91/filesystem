<?php

namespace tests\Unit\Driver;

use Filesystem\FileUpload\Driver\FtpDriver;

class FtpDriverTest extends \PHPUnit_Framework_TestCase
{
	public function getDriver()
	{
		$config = [
			'host'      => "127.0.0.1",
			'port'      => 21,
			'username'  => "vagrant",
			'password'  => "vagrant",
			'root'      => "../vagrantuploads/ftpdriver/",
			'ssl'       => false,
			'passive'   => true,
			'timeout'   => 90
		];

		return new FtpDriver($config);
	}

	public function testItConnectsAndDisconnects()
	{
		$ftpDriver = $this->getDriver();

		// Connects automatically on construction
		$this->assertTrue($ftpDriver->isConnected());

		$ftpDriver->disconnect();

		$this->assertFalse($ftpDriver->isConnected());

		$ftpDriver->connect();

		$this->assertTrue($ftpDriver->isConnected());
	}

	public function testItPutsAndGets()
	{
		$ftpDriver = $this->getDriver();

		$tmpFile = "temp.txt";

		file_put_contents($tmpFile, "test content");

		$ftpDriver->put($tmpFile, "foo.txt");

		unlink($tmpFile);

		$ftpDriver->get("foo.txt", new \SplFileInfo($tmpFile));

		$this->assertEquals("test content", file_get_contents($tmpFile));

		$this->assertFileExists('/home/vagrant/vagrantuploads/ftpdriver/foo.txt');
	}

	public function testItReadsAndWrites()
	{
		$ftpDriver = $this->getDriver();

		$ftpDriver->write("test.txt", "test content");

		$remoteContent = $ftpDriver->read("test.txt");

		$this->assertEquals("test content", $remoteContent);
	}

	public function testItCanCreateDirectories()
	{
		$ftpDriver = $this->getDriver();

		$ftpDriver->makeDirectory("createdir");

		$directoryContents = $ftpDriver->listContents('.');

		$this->assertContains("createdir", $directoryContents);
	}

	public function testItListsContents()
	{
		$ftpDriver = $this->getDriver();

		$directoryContents = $ftpDriver->listContents('.');

		$expected = ["foo.txt", "test.txt", "createdir"];

		sort($expected);

		sort($directoryContents);

		$this->assertEquals($expected, $directoryContents);
	}

	public function testItCantPutDirectory()
	{
		$ftpDriver = $this->getDriver();

		$this->expectException("Exception");

		$ftpDriver->putDirectory('tests', 'dr');
	}

	public function testItCanGetSize()
	{
		$ftpDriver = $this->getDriver();

		$actualSize = $ftpDriver->getSize("foo.txt");

		$expectedSize = filesize("/home/vagrant/vagrantuploads/ftpdriver/foo.txt");

		$this->assertEquals($expectedSize, $actualSize);
	}
}