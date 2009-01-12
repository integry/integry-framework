<?php

ClassLoader::import('framework.response.Response');

/**
 * JSON response
 *
 * @package framework.response
 * @author	Integry Systems
 */
class ObjectFileResponse extends Response
{
	private $file;
	private $deleteFile = false;

	public function __construct(ObjectFile $objectFile)
	{
		if ($objectFile->isLocalFile())
		{
			$this->setHeader('Cache-Control', 'no-cache, must-revalidate');
			$this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
			$this->setHeader('Content-type', $objectFile->getMimeType());
			$this->setHeader('Content-Disposition', 'attachment; filename="'.$objectFile->getBaseName().'"');
			$this->setHeader('Content-Length', (string)$objectFile->getSize());

			$this->file = $objectFile;
		}
		else
		{
			$this->setHeader('Location', $objectFile->filePath->get());
		}
	}

	public function deleteFileOnComplete($delete = true)
	{
		$this->deleteFile = $delete;
	}

	public function sendData()
	{
		@ini_set('max_execution_time', 0);
		$f = fopen($this->file->getPath(), 'r');

		while (!feof($f))
		{
			echo fread($f, 4096);
		}

		fclose($f);
	}

	public function getData()
	{
		return $this->content;
	}

	public function __destruct()
	{
		if ($this->deleteFile)
		{
			$path = $this->file->getPath();
			if (file_exists($path))
			{
				unlink($path);
			}
		}
	}
}

?>
