<?php

namespace tests\Unit\Driver;

use Filesystem\FileUpload\Driver\S3Driver;

class S3DriverTest extends \PHPUnit_Framework_TestCase
{
	public function getDriver()
	{
		$config = [
			'bucket'        => "s3-test-bucket",
			'prefix'        => "s3driver",
			'key'           => "s3-test-key",
			'secret'        => "s3-test-secret",
			'version'       => 'latest',
			'region'        => 'eu-west-1',
			'encrypt'       => true,
		];

		return new S3Driver($config);
	}

	public function testItConnectsAndDisconnects()
	{
		$s3Driver = $this->getDriver();

		$s3Driver->disconnect();

		$s3Driver->connect();
	}

	public function testItPutsAndGets()
	{
		$s3Driver = $this->getDriver();

		$tmpFile = "/home/vagrant/vagrantuploads/s3driver/temp.txt";

		file_put_contents($tmpFile, "test content");

		$s3Driver->put($tmpFile, "foo.txt");

		unlink($tmpFile);

		$s3Driver->get("foo.txt", new \SplFileInfo($tmpFile));

		$this->assertEquals("test content", file_get_contents($tmpFile));
	}

	public function testItPutsDirectoryAndGets()
	{
		$s3Driver = $this->getDriver();

		$tmpFile1 = "/home/vagrant/vagrantuploads/s3driver/temp1.txt";

		file_put_contents($tmpFile1, "test content1");

		$tmpFile2 = "/home/vagrant/vagrantuploads/s3driver/temp2.txt";

		file_put_contents($tmpFile2, "test content2");

		$s3Driver->putDirectory('/home/vagrant/vagrantuploads/s3driver', 'bulk');

		unlink($tmpFile1);

		unlink($tmpFile2);

		$s3Driver->get("bulk/temp1.txt", new \SplFileInfo($tmpFile1));

		$this->assertEquals("test content1", file_get_contents($tmpFile1));

		$s3Driver->get("bulk/temp2.txt", new \SplFileInfo($tmpFile2));

		$this->assertEquals("test content2", file_get_contents($tmpFile2));
	}

	public function testItReadsAndWrites()
	{
		$s3Driver = $this->getDriver();

		$s3Driver->write("test.txt", "test content");

		$remoteContent = $s3Driver->read("test.txt");

		$this->assertEquals("test content", $remoteContent);
	}

	public function testItListsContents()
	{
		$s3Driver = $this->getDriver();

		$s3Driver->write("foo.txt", "test content");
		$s3Driver->write("test.txt", "test content");
		$s3Driver->write("foobar.txt", "test content");

		$directoryContents = $s3Driver->listContents('*foo*');

		$expected = ["foo.txt", "foobar.txt"];

		sort($expected);

		sort($directoryContents);

		$this->assertEquals($expected, $directoryContents);
	}

	public function testMakeDirectoryReturnsFalse()
	{
		$s3Driver = $this->getDriver();

		$this->assertEquals(false, $s3Driver->makeDirectory('dir'));
	}

	public function testItCanGetSize()
	{
		$s3Driver = $this->getDriver();

		$actualSize = $s3Driver->getSize("foo.txt");

		$expectedSize = filesize("/home/vagrant/vagrantuploads/s3driver/temp.txt");

		$this->assertEquals($expectedSize, $actualSize);
	}
}