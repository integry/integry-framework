<?php

ClassLoader::import('framework.response.*');

/**
 * Class for creating response from raw output.
 *
 * @package	framework.response
 */
class RawResponse extends Response {

	/**
	 * Stores content
	 */
	private $content;


	/**
	 * @param string $content Raw output
	 */
	public function __construct($content = '') {
		$this->setContent($content);
	}

	/**
	 * Sets content to response
	 *
	 * @param string $content Content
	 * @param boolean $append True to append content, false to replace
	 * @return void
	 */
	public function setContent($content, $append = false) {
		if ($append) {
			$this->content .= $content;
		} else {
			$this->content = $content;
		}
	}

	/**
	 * Gets content from response
	 *
	 * @return string Content of response
	 */
	public function getContent() {
		return $this->content;
	}
	
	
	public function getData() {
		return $this->content;
	}

}

?>