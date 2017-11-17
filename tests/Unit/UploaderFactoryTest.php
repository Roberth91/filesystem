<?php

namespace tests\Unit;

use Filesystem\FileUpload\Configuration\Configuration;
use Filesystem\FileUpload\UploaderFactory;

class UploaderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function getFtpDriver()
    {
        $uploaderFactory = new UploaderFactory();

        $config = new Configuration('127.0.0.1', 'vagrant', 'vagrant', '/vagrantuploads/ftpdriver');

        return $uploaderFactory->getUploader('ftp', $config);
    }

    public function getS3Driver()
    {
        $uploaderFactory = new UploaderFactory();

        $config = new Configuration('s3-test-bucket:eu-west-1', 's3-test-key', 's3-test-secret', 's3driver/encrypt', null, true);

        return $uploaderFactory->getUploader('s3', $config);
    }

    public function testFtpDriverFailsConnect()
    {
        $uploaderFactory = new UploaderFactory();

        $config = new Configuration('127.0.0.1:1', 'vagrant', 'vagrant', '');

        $this->expectException('\Exception');

        $driver = $uploaderFactory->getUploader('ftp', $config);
        $driver->connect();
    }

    public function testS3DriverConnects()
    {
        $uploaderFactory = new UploaderFactory();

        $config = new Configuration('s3-test-bucket', 's3-test-key', 's3-test-secret', 's3driver');

        $driver = $uploaderFactory->getUploader('s3', $config);
        $driver->connect();
        $driver->disconnect();
    }

    public function testLocalDriverConnectsAndPuts()
    {
        $uploaderFactory = new UploaderFactory();

        $config = new Configuration('127.0.0.1', 'vagrant', 'vagrant', '/vagrantuploads/localdriver');

        $driver = $uploaderFactory->getUploader('local', $config);
        $driver->connect();
        $driver->uploadDirectory('/home/vagrant/vagrantuploads/localdriver/*', '/home/vagrant/vagrantuploads/localdriveroutput');
        $driver->disconnect();
    }

    public function testDefaultDriverDoesntConnect()
    {
        $uploaderFactory = new UploaderFactory();

        $config = new Configuration('127.0.0.1', 'vagrant', 'vagrant', '/vagrantuploads/ftpdriver');

        $this->expectException("InvalidArgumentException");

        $driver = $uploaderFactory->getUploader('', $config);
        $driver->connect();
    }

    public function testSftpDriverConnects()
    {
        $uploaderFactory = new UploaderFactory();

        $sshKey = file_get_contents(__DIR__.'../../files/sshKey.txt');

        $config = new Configuration('127.0.0.1', 'vagrant', 'vagrant', '/home/vagrant/vagrantuploads/sftpdriver', $sshKey);

        $sftp = $uploaderFactory->getUploader('sftp', $config);
        $sftp->connect();
        $sftp->listDirectory('');
        $sftp->disconnect();
    }

    public function testSftpDriverConnectsWithoutPassword()
    {
        $uploaderFactory = new UploaderFactory();

        $sshKey = file_get_contents(__DIR__.'../../files/sshKey.txt');

        $config = new Configuration('127.0.0.1', 'vagrant', null, '/home/vagrant/vagrantuploads/sftpdriver', $sshKey);

        $sftp = $uploaderFactory->getUploader('sftp', $config);
        $sftp->connect();
        $sftp->listDirectory('');
        $sftp->disconnect();
    }

    public function testSftpDriverFailsWithoutKey()
    {
        $uploaderFactory = new UploaderFactory();

        $config = new Configuration('127.0.0.1', 'vagrant', '', '/home/vagrant/vagrantuploads/sftpdriver');

        $sftp = $uploaderFactory->getUploader('sftp', $config);
        $sftp->connect();
        $sftp->disconnect();
    }

    public function testItConnectsAndDisconnects()
    {
        $ftpDriver = $this->getFtpDriver();

        $ftpDriver->disconnect();

        $ftpDriver->connect();
    }

    public function testItUploadsAndDownloads()
    {
        $ftpDriver = $this->getFtpDriver();

        $ftpDriver->upload(new \SplFileInfo('tests/files/localinput.txt'), "temp.txt");

        $ftpDriver->download("temp.txt", new \SplFileInfo('tests/files/localoutput.txt'));

        $this->assertFileExists("tests/files/localoutput.txt");

        // Cleanup
        unlink("tests/files/localoutput.txt");
    }

    public function testItReadsAndWrites()
    {
        $ftpDriver = $this->getFtpDriver();

        $ftpDriver->write("test content", "test.txt");

        $remoteContent = $ftpDriver->read("test.txt");

        $this->assertEquals("test content", $remoteContent);
    }


    public function testItListsContents()
    {
        $ftpDriver = $this->getFtpDriver();

        $ftpDriver->write("test content", "test.txt");
        $ftpDriver->write("test content", "foo.txt");
        $ftpDriver->write("test content", "temp.txt");

        $directoryContents = $ftpDriver->listDirectory('');

        $resultsArr = $directoryContents;

        $expected = ["foo.txt", "test.txt", "temp.txt", "createdir"];

        sort($expected);

        sort($resultsArr);

        $this->assertEquals($expected, $resultsArr);
    }

    public function testItCanMakeDirectory()
    {
        $ftpDriver = $this->getFtpDriver();

        $tmpFile1 = "temp1.txt";

        file_put_contents($tmpFile1, "test content1");

        $tmpFile2 = "temp2.txt";

        file_put_contents($tmpFile2, "test content2");

        $ftpDriver->makeDirectory('created');

        $this->assertEquals(true, is_dir('../vagrantuploads/ftpdriver/created'));

        unlink($tmpFile1);

        unlink($tmpFile2);

        rmdir('../vagrantuploads/ftpdriver/created');
    }

    public function testNotImplementedException()
    {
        $ftpDriver = $this->getFtpDriver();

        $this->expectException("Exception");

        $ftpDriver->uploadDirectory('ftpdriver', 'ftpdriveroutput');
    }

    public function testItCanGetSize()
    {
        $ftpDriver = $this->getFtpDriver();

        $actualSize = $ftpDriver->fileSize("foo.txt");

        $expectedSize = filesize("/home/vagrant/vagrantuploads/ftpdriver/foo.txt");

        $this->assertEquals($expectedSize, $actualSize);
    }

    public function testS3PutsWithEncryption()
    {
        $driver = $this->getS3Driver();

        $driver->write("test content", "test.txt");
    }
}