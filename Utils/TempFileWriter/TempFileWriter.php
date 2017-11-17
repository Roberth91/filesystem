<?php

namespace Filesystem\Utils\TempFileWriter;

/**
 * Class TempFileWriter
 *
 * This class writes input
 * to a temporary file
 * stored in memory
 *
 * It returns the relative
 * location of the tmp
 * file as a string
 *
 * The class will also run
 * a clean up function
 * through fclose()
 *
 */
class TempFileWriter
{
	private $tmpFileHandle;

	public function __construct($content = null)
	{
		$this->tmpFileHandle = tmpfile();

		if($content !== null)
		{
			$this->write($content);
		}
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	public function write($data)
	{
		return fwrite($this->tmpFileHandle, $data);
	}

	/**
	 * Returns new tempfile
	 * @return \SplFileInfo
	 */
	public function getTempFile()
	{
		$meta = stream_get_meta_data($this->tmpFileHandle);

		return new \SplFileInfo($meta['uri']);
	}

	/**
	 * Closes the stream,
	 * removes tmpfile
	 *
	 */
	public function releaseTempFile()
	{
		if($this->tmpFileHandle)
		{
			fclose($this->tmpFileHandle);

			$this->tmpFileHandle = null;
		}
	}

	/**
	 *
	 */
	public function __destruct()
	{
		$this->releaseTempFile();
	}
}