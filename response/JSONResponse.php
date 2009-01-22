<?php

ClassLoader::import("framework.response.Response");

/**
 * JSON response
 *
 * @package framework.response
 * @author	Integry Systems
 */
class JSONResponse extends Response
{
	private $content;

	private $data;

	public function __construct($data, $status = false, $message = false)
	{
		$this->setHeader('Cache-Control', 'no-cache, must-revalidate');
		$this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
		$this->setHeader('Content-type', 'text/javascript;charset=utf-8');

		if($message)
		{
			$data['message'] = $message;
		}

		if($status)
		{
			$data['status'] = strtolower($status);
		}

		$this->data = $data;
	}

	public function flush($string)
	{
		if (!headers_sent())
		{
			$this->sendHeaders();
		}

		ob_end_flush();
		echo $string;
		flush();
	}

	public function getValue()
	{
		return $this->data;
	}

	public function getData()
	{
		if (!$this->content)
		{
			$this->content = @json_encode($this->data);
		}

		return $this->content;
	}
}

?>
