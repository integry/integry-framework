<?php

/**
 * Response base class.
 *
 * Response is a data carrier between different actions and different parts of applications
 *
 * @package	framework.response
 */
abstract class Response {

	/**
	 * Stores raw headers
	 */
	private $rawHeaderData = array();

	/**
	 * Stores headers
	 */
	private $headerData = array();

	/**
	 * Sets raw header to response
	 *
	 * @param string $header Raw header to send
	 * @return void
	 */
	public function setRawHeader($header) {
		$this->rawHeaderData[] = $header;
	}

	/**
	 * Removes all raw headers from response
	 *
	 * @return void
	 */
	public function unsetAllRawHeaders() {
		$this->rawHeaderData = array();
	}

	/**
	 * Sets header to response
	 *
	 * @param string $name Name of header to set
	 * @param string $value Value
	 * @return void
	 */
	public function setHeader($name, $value) {
		$this->headerData[$name] = $value;
	}

	/**
	 * Gets header from response
	 *
	 * @param string $name Name of header
	 * @return mixed null if there is no $name value
	 */
	public function getHeader($name) {
		if ($this->isHeaderSet($name)) {
			return $this->headerData[$name];
		}
		return null;
	}

	/**
	 * Removes header from response
	 *
	 * @param string $name Name of header
	 * @return void
	 */
	public function unsetHeader($name) {
		if ($this->isHeaderSet($name)) {
			unset($this->headerData[$name]);
		}
	}

	/**
	 * Removes all headers from response
	 *
	 * @return void
	 */
	public function unsetAllHeaders() {
		$this->headerData = array();
	}

	/**
	 * Checks if header set
	 *
	 * @param string $name Name of header
	 * @return boolean true if there is $name header, false otherwise
	 */
	public function isHeaderSet($name) {
		return isset($this->headerData[$name]);
	}

	/**
	 * Sends headers
	 *
	 * @return void
	 * @throws HeadersSentException if headers have been already sent
	 */
	public function sendHeaders() {
		if (headers_sent()) {
			//throw new HeadersSentException();
		}
		/* Raw header */
		foreach ((array) $this->rawHeaderData as $header) {
			header($header);
		}
		/* Headers */
		foreach ((array) $this->headerData as $name => $value) {
			header("$name: $value");
		}
	}
	
	abstract public function getData();
}

?>