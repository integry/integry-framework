<?php

ClassLoader::import('framework.request.*');
ClassLoader::import('framework.renderer.*');
ClassLoader::import('framework.response.*');
ClassLoader::import('framework.controller.*');
ClassLoader::import('framework.ApplicationException');

/**
 * Class for running an application.
 *
 * This class handles the whole application execution life-cycle:
 *  + request data collection
 *  + action dispatching
 *  + model loading
 *  + rendering output
 *
 * The purpose is to automate recurring tasks involved into application execution
 * and result presentation and let a developer to concentrate on a model.
 *
 * A common application execution:
 * <code>
 * try {
 * 		$myApplication = new Application();
 * 		$myApplication->run();
 * } catch(ApplicationException $ex) {
 * 		echo $ex->getMessage();
 * }
 * </code>
 *
 * A few customizations are available before application is executed.
 * Note: Avoid any output before application is executed because this will cause a response exception.
 *
 * @see self::setDefaultControllerName()
 * @see self::setRenderer()
 * @see self::setRequestFormatter()
 *
 * @package framework
 * @author Integry Systems
 *
 */
class Application
{
	protected $routerClass = 'Router';
	protected $requestClass = 'Request';
	protected $rendererClass = 'PHPRenderer';

	/**
	 * @var Router
	 */
	/* protected $router = null; */

	/**
	 * Request instance
	 * @var Request
	 */
	protected $request = null;

	/**
	 * Renderer instance (usualy a template renderer)
	 * @var Renderer
	 */
	protected $renderer = null;

	/**
	 * Default controller name. It can be changed during a runtime (before run()'ing an
	 * application)
	 *
	 * @var string
	 * @see self::setDefaultControllerName()
	 */
	private $defaultControllerName = "index";

	protected $controllerDirectories = array();

	/**
	 * Application constructor.
	 *
	 * @see self::getInstance()
	 */
	public function __construct()
	{
		$this->request = new $this->requestClass();
	}

	/**
	 * Sets default controller name
	 *
	 * @param string $controller Name of controller
	 * @return void
	 */
	public function setDefaultControllerName($controllerName)
	{
		$this->defaultControllerName = $controllerName;
	}

	/**
	 * Gets default controller name
	 *
	 * @return string Controller name
	 */
	public function getDefaultControllerName()
	{
		return $this->defaultControllerName;
	}

	/**
	 * Sets renderer for application
	 *
	 * @param Renderer $renderer Instance of Renderer
	 * @return void
	 */
	public function setRenderer(Renderer $renderer)
	{
		$this->renderer = $renderer;
	}

	public function setRendererClass($className)
	{
		$this->rendererClass = $className;
	}

	/**
	 * Gets renderer for application
	 *
	 * @return Renderer
	 */
	public function getRenderer()
	{
		if (is_null($this->renderer))
		{
			$class = $this->rendererClass;
			$this->renderer = new $class($this);
		}
		return $this->renderer;
	}

	/**
	 * Gets (or creates) a Request instance for accessing request data
	 *
	 * @return Request
	 */
	public function getRequest()
	{
		if ($this->request == null)
		{
			$this->request = $this->router->getRequest();
		}

		return $this->request;
	}

	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Return a router instance
	 *
	 * @return Request
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Executes an application and generates an output if any.
	 *
	 * @throws ApplicationException Rethrowed framework level exception (should be handled manually)
	 */
	public function run($redirect = false)
	{
		if (!$redirect)
		{
			$this->router->mapToRoute($this->router->getRequestedRoute(), $this->request);
		}

		$controllerName = $this->getRequest()->getControllerName();
		$actionName = $this->getRequest()->getActionName();

		if (empty($controllerName))
		{
			$controllerName = $this->getDefaultControllerName();
		}
		if (empty($actionName))
		{
			$actionName = Controller::DEFAULT_ACTION;
		}

		$output = '';

		/* Execute an action of some controller */
		try
		{
			$controllerInstance = $this->getControllerInstance($controllerName);
			$this->controllerInstance = $controllerInstance;

			$response = $this->execute($controllerInstance, $actionName);
			$response->sendHeaders();
			$response->sendData();

			if ($response instanceof Renderable)
			{
				$output = $this->render($controllerInstance, $response);

				// using layout defined in a controller for action output
				if ($controllerInstance->getLayout() != null && !($response instanceof BlockResponse))
				{
					$this->getRenderer()->set("ACTION_VIEW", $output);
					$output = $this->getRenderer()->render($this->getLayoutPath($controllerInstance->getLayout()));
				}
			}
			else if ($response instanceof InternalRedirectResponse)
			{
				$this->getRequest()->setControllerName($response->getControllerName());
				$this->getRequest()->setActionName($response->getActionName());
				$this->getRequest()->setValueArray($response->getParamList());

				return $this->run(true);
			}
			else
			{
				$output = $response->getData();
			}

		}
		catch(ApplicationException $ex)
		{
			throw $ex;
		}

		$this->sendOutput($output);
	}

	protected function sendOutput($output)
	{
		echo $output;
	}

	public function getBlockContent($name)
	{
		$output = '';
		foreach ($this->renderBlockContainer($name) as $rendered)
		{
			$output .= $rendered['output'];
		}

		return $output;
	}

	protected function renderBlockContainer($name)
	{
		$render = array();
		foreach ($this->controllerInstance->getBlocks($name) as $block)
		{
			$render[] = array('output' => $this->renderBlock($block, $this->controllerInstance));
		}

		return $render;
	}

	protected function renderBlock($block, Controller $controllerInstance)
	{
		$controllerInstance->setBlockName($block['container']);
		$block['response'] = $controllerInstance->getBlockResponse($block);

		if (!$block['response'])
		{
			$blockOutput = '';
			$this->getRenderer()->appendValue($block['container'], $blockOutput);
			return $blockOutput;
		}

		if (!($block['response'] instanceof BlockResponse))
		{
			throw new ApplicationException("Unknown response flom a block");
		}

		$this->postProcessResponse($block['response'], $controllerInstance);
		//$blockOutput = $this->getRenderer()->process($block['response'], $block['view']);

		// dynamically loaded blocks
		if (substr($block['view'], 0, 2) == './')
		{
			// trim .tpl
			$block['view'] = substr($block['view'], 0, -4);
			$block['view'] = $this->getView($block['call'][0]->getControllerName(), $block['view']);
		}

		$blockOutput = $this->getRenderer()->process($block['response'], $block['view']);
		$this->getRenderer()->appendValue($block['container'], $blockOutput);

		return $blockOutput;
	}

	/**
	 * Executes controllers action and returns response
	 *
	 * @param Response $response Response object instance
	 * @return void
	 */
	protected function postProcessResponse(Response $response, Controller $controllerInstance)
	{
	}

	/**
	 * Executes controllers action and returns response
	 *
	 * @param string $controllerName Controller name
	 * @param string $actionName Action name
	 * @return Response
	 * @throws ApplicationException if error situation occurs
	 */
	protected function execute($controllerInstance, $actionName)
	{
		try
		{
			$response = $controllerInstance->execute($actionName, $this->getRequest());
			$this->processResponse($response);
			$this->postProcessResponse($response, $controllerInstance);
			return $response;
		}
		catch(ApplicationException $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Gets specified controller instance
	 *
	 * @param string $controllerName Controller name
	 * @return Controller
	 * @throws ControllerNotFoundException if controller does not exist
	 */
	protected function getControllerInstance($controllerName)
	{
		$controllerPath = explode(".", $controllerName);
		$pathLength = count($controllerPath);
		$className = ucfirst($controllerPath[$pathLength - 1]).'Controller';
		$controllerPath[$pathLength - 1] = $className;
		$controllerPath = implode("/", $controllerPath);

		foreach ($this->getControllerDirectories() as $dir)
		{
			$controllerSystemPath = $dir . '/' . $controllerPath . '.php';
			if (file_exists($controllerSystemPath))
			{
				$this->controllerDirectories[$controllerName] = $dir;

				include_once($controllerSystemPath);

				$refl = new ReflectionClass($className);
				if (!$refl->isInstantiable())
				{
					continue;
				}
				$instance = new $className($this);
				$instance->setControllerName($controllerName);
				return $instance;
			}
		}

		throw new ControllerNotFoundException($controllerName);
	}

	protected function getControllerDirectories()
	{
		$controllerSystemPaths = array();
		$controllerSystemPaths[] = ClassLoader::getRealPath("application.controller");
		return $controllerSystemPaths;
	}

	/**
	 * Processes response
	 *
	 * @param Response $response
	 * @return void
	 * @throws ApplicationException if error situation occurs
	 */
	protected function processResponse(Response $response)
	{
		/* Handle redirect to another action */
		if ($response instanceof ActionRedirectResponse)
		{
			try
			{
				$this->getActionRedirectResponseUrl($response);
			}
			catch(ApplicationException $ex)
			{
				throw $ex;
			}
		}

		/* Handle composite response */
		if ($response instanceof CompositeResponse)
		{
			try
			{
				$responses = array();
				foreach ($response->getRequestedActionList() as $outputHandle => $location)
				{
					$controllerName = $location[CompositeResponse::CONTROLLER_HANDLE];
					$actionName = $location[CompositeResponse::ACTION_HANDLE];
					$instance = $this->getControllerInstance($controllerName);
					$responses[$outputHandle] = array($this->execute($instance, $actionName), $instance, $actionName);
				}

				foreach (array_merge($responses, $response->getResponseList()) as $outputHandle => $data)
				{
					list($includedResponse, $controller, $actionName) = $data;
					$this->processResponse($includedResponse);
					$this->postProcessResponse($includedResponse, $controller);

					if ($includedResponse instanceof Renderable)
					{
						$response->set($outputHandle, $this->render($controller, $includedResponse, $actionName));
					}
					else
					{
						$response->setResponse($outputHandle, $includedResponse);
					}
				}
			}
			catch(ApplicationException $ex)
			{
				throw $ex;
			}
		}
	}

	public function getActionRedirectResponseUrl(ActionRedirectResponse $response)
	{
		$paramList = array("controller" => $response->getControllerName(), "action" => $response->getActionName());
		$paramList = array_merge($paramList, $response->getParamList());
		$response->setRedirectURL($this->router->createURL($paramList));

		return $response->getRedirectURL();
	}

	/**
	 * Renders response from controller action
	 *
	 * @param string $controllerInstance Controller
	 * @param Response $response Response to render
	 * @return string Renderer content
	 * @throws ViewNotFoundException if view does not exists for specified controller
	 */
	protected function render(Controller $controllerInstance, Response $response, $actionName = null)
	{
		$controllerName = $this->getRequest()->getControllerName();
		$actionName = $actionName ? $actionName : $this->getRequest()->getActionName();

		try
		{
			return $this->getRenderer()->process($response, $this->getView($controllerName, $actionName));
		}
		catch(ViewNotFoundException $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Gets view path for specified controllers action
	 *
	 * @param string $controllerName Controller name
	 * @param string $actionName Action name
	 * @return string View path
	 */
	public function getView($controllerName, $actionName)
	{
		$controllerName = str_replace('\\', '/', $controllerName);
		$dir = dirname($this->controllerDirectories[str_replace('/', '.', $controllerName)]) . '/view';
		return $dir . '/' . str_replace('.', '/', $controllerName) . '/' . $actionName . '.' . $this->getRenderer()->getViewExtension();
	}

	/**
	 * Gets a physical layout template path
	 *
	 * @param string $layout layout handle (filename without extension)
	 * @return string
	 */
	public function getLayoutPath($layout)
	{
		return ClassLoader::getRealPath('application.view.layout.' . $layout) . '.' . $this->getRenderer()->getViewExtension();
	}

	public function __get($name)
	{
		switch ($name)
	  	{
			case 'router':
				$this->router = new $this->routerClass($this->request);
				return $this->router;
			break;

			default:
			break;
		}
	}
}
?>
