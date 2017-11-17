<?php

namespace tests\Unit\TempFileWriter;

use Filesystem\Utils\TempFileWriter\TempFileWriter;
use PHPUnit_Framework_TestCase;

class TempFileWriterTest extends PHPUnit_Framework_TestCase
{

	public function testTempFileWriter()
	{
		$tmpFileWriter = new TempFileWriter("abc");

		$tmpFile = $tmpFileWriter->getTempFile();

		$this->assertFileExists($tmpFile->getPath() . "/" . $tmpFile->getFilename());

		$contents = file_get_contents($tmpFile);

		$this->assertEquals("abc", $contents);

		$tmpFileWriter->releaseTempFile();

		$tmpFileWriter = null;

		$this->assertFileNotExists($tmpFile->getPath() . "/" . $tmpFile->getFilename());
	}

}