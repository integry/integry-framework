<?php

ClassLoader::import('framework.response.Response');
ClassLoader::import('framework.renderer.Renderable');

/**
 * Allows CompositeResponse to set one renderable response
 *
 * @package	framework.response
 * @author Integry Systems
 */
class CompositeActionResponse extends CompositeResponse implements Renderable
{
	protected $mainResponse;
	
	public function setMainResponse(Renderable $response)
	{
		$this->mainResponse = $response;
	}

	public function render(Renderer $renderer, $view)
	{
		if (!$this->mainResponse)
		{
			$this->mainResponse = new ActionResponse();
		}
		
		foreach ($this->getData() as $key => $value)
		{
			$this->mainResponse->set($key, $value);
		}
		
		return $this->mainResponse->render($renderer, $view);
	}

	public function getData()
	{
		return $this->data;
	}

}

?>
