<?php

namespace tests\Unit\Driver;

use Filesystem\FileUpload\Driver\SftpDriver;

class SftpDriverTest extends \PHPUnit_Framework_TestCase
{
	public function getDriver()
	{
		$config = [
			'host'      => "127.0.0.1",
			'port'      => 22,
			'username'  => "vagrant",
			'password'  => "vagrant",
			'root'      => "../vagrantuploads/ftpdriver/",
			'ssl'       => false,
			'passive'   => true,
			'timeout'   => 90
		];

		return new SftpDriver($config);
	}

	public function testItConnectsAndDisconnects()
	{

	}

	public function testItPutsAndGets()
	{
		$sftpDriver = $this->getDriver();

		$tmpFile = "temp.txt";

		file_put_contents($tmpFile, "test content");

		$sftpDriver->put($tmpFile, "foo.txt");

		unlink($tmpFile);

		$sftpDriver->get("foo.txt", new \SplFileInfo("/home/vagrant/vagrantuploads/sftpdriver/temp.txt"));

		$this->assertFileExists("/home/vagrant/vagrantuploads/sftpdriver/temp.txt");
	}

	public function testItReadsAndWrites()
	{
		$sftpDriver = $this->getDriver();

		$sftpDriver->write("test.txt", "test content");

		$remoteContent = $sftpDriver->read("test.txt");

		$this->assertEquals("test content", $remoteContent);
	}

	public function testItGetsSize()
	{
		$sftpDriver = $this->getDriver();

		$actualSize = $sftpDriver->getSize("foo.txt");

		$expectedSize = filesize("/home/vagrant/vagrantuploads/sftpdriver/temp.txt");

		$this->assertEquals($expectedSize, $actualSize);
	}

	public function testItCanCreateDirectories()
	{
		$sftpDriver = $this->getDriver();

		$sftpDriver->makeDirectory("/home/vagrant/vagrantuploads/sftpdriver/createdir");

		$directoryContents = $sftpDriver->listContents('.');

		$this->assertContains("createdir/", $directoryContents);
	}

	public function testItListsContents()
	{
		$sftpDriver = $this->getDriver();

		$directoryContents = $sftpDriver->listContents('.');

		$expected = ["foo.txt", "test.txt", "createdir/"];

		sort($expected);

		sort($directoryContents);

		$this->assertEquals($expected, $directoryContents);
	}

	public function testItCantPutDirectory()
	{
		$sftpDriver = $this->getDriver();

		$this->expectException("Exception");

		$sftpDriver->putDirectory('tests', 'dir');
	}
}