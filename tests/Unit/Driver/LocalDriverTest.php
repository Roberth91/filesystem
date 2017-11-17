<?php

namespace tests\Unit\Driver;

use Filesystem\FileUpload\Driver\LocalDriver;

class LocalDriverTest extends \PHPUnit_Framework_TestCase
{
	public function getDriver()
	{
		$config = [
			'root'	=> "/home/vagrant/vagrantuploads/localdriver",
		];

		return new LocalDriver($config);
	}

	public function testItConnectsAndDisconnects()
	{
		$localDriver = $this->getDriver();

		$localDriver->disconnect();

		$localDriver->connect();
	}

	public function testItPutsAndGets()
	{
		$localDriver = $this->getDriver();

		$tmpFile = "/home/vagrant/vagrantuploads/localdriver/temp.txt";

		file_put_contents($tmpFile, "test content");

		$localDriver->put($tmpFile, "foo.txt");

		unlink($tmpFile);

		$localDriver->get("foo.txt", new \SplFileInfo($tmpFile));

		$this->assertEquals("test content", file_get_contents($tmpFile));
	}

	public function testItReadsAndWrites()
	{
		$localDriver = $this->getDriver();

		$localDriver->write("test.txt", "test content");

		$remoteContent = $localDriver->read("test.txt");

		$this->assertEquals("test content", $remoteContent);
	}


	public function testItListsContents()
	{
		$localDriver = $this->getDriver();

		$localDriver->write("test.txt", "test content");
		$localDriver->write("foo.txt", "test content");
		$localDriver->write("temp.txt", "test content");

		$directoryContents = $localDriver->listContents('/*');

		$resultsArr = $directoryContents;

		$expected = ["foo.txt", "test.txt", "temp.txt"];

		sort($expected);

		sort($resultsArr);

		$this->assertEquals($expected, $resultsArr);
	}

	public function testItCanMakeAndPutDirectory()
	{
		$localDriver = $this->getDriver();

		$tmpFile1 = "/home/vagrant/vagrantuploads/localdriver/temp1.txt";

		file_put_contents($tmpFile1, "test content1");

		$tmpFile2 = "/home/vagrant/vagrantuploads/localdriver/temp2.txt";

		file_put_contents($tmpFile2, "test content2");

		$localDriver->makeDirectory('../localdriveroutput');

		$this->assertEquals(true, is_dir('/home/vagrant/vagrantuploads/localdriveroutput'));

		$localDriver->putDirectory('/home/vagrant/vagrantuploads/localdriver/*', '/home/vagrant/vagrantuploads/localdriveroutput');

		unlink($tmpFile1);

		unlink($tmpFile2);
	}

	public function testItCanGetSize()
	{
		$localDriver = $this->getDriver();

		$actualSize = $localDriver->getSize("foo.txt");

		$expectedSize = filesize("/home/vagrant/vagrantuploads/localdriver/foo.txt");

		$this->assertEquals($expectedSize, $actualSize);
	}

}