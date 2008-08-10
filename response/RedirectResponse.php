<?php

ClassLoader::import('framework.response.Response');

/**
 * Class for creating redirect response.
 *
 * @package	framework.response
 * @author	Integry Systems
 */
class RedirectResponse extends Response
{
	/**
	 * @param string $url URL location
	 */
	public function __construct($url)
	{
		$this->setRedirectUrl($url);
	}

	/**
	 * Sets URL to redirect to
	 *
	 * @param string $url URL location
	 * @return void
	 */
	public function setRedirectURL($url)
	{
		$this->setHeader('Location', $url);
	}

	/**
	 * Gets URL of redirect
	 *
	 * @return string URL location
	 */
	public function getRedirectURL()
	{
		return $this->getHeader('Location');
	}

	public function getData()
	{
		return ;
	}
}

?>