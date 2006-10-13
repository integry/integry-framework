<?php

ClassLoader::import('framework.request.*');
ClassLoader::import('framework.renderer.*');
ClassLoader::import('framework.response.*');
ClassLoader::import('framework.controller.*');

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
 * 		$myApplication = Application::getInstance();
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
 * @author Saulius Rupainis <saulius@integry.net>
 *
 */
class Application
{

	/**
	 * Application instance (based on a singleton pattern)
	 */
	private static $instance = null;

	/**
	 * @var Router
	 */
	private $router = null;

	/**
	 * Request instance
	 * @var Request
	 */
	private $request = null;

	/**
	 * Renderer instance (usualy a template renderer)
	 * @var Renderer
	 */
	private $renderer = null;

	/**
	 * Default controller name. It can be changed during a runtime (before run()'ing an
	 * application)
	 *
	 * @var string
	 * @see self::setDefaultControllerName()
	 */
	private $defaultControllerName = "index";


	/**
	 * Application constructor. Use self::getInstance() method instead of "new" operator
	 *
	 * @see self::getInstance()
	 */
	final private function __construct()
	{
		$this->request = new Request();
		$this->router = Router::getInstance();
	}

	/**
	 * Returns an instance of Application
	 *
	 * Method prevents of creating multiple application instances during one request
	 *
	 * @return Application
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new Application();
		}
		return self::$instance;
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

	/**
	 * Gets renderer for application
	 *
	 * @return Renderer
	 */
	public function getRenderer()
	{
		if (is_null($this->renderer))
		{
			$this->renderer = new TemplateRenderer($this->router);
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

	/**
	 * Executes an application and generates an output if any.
	 *
	 * @throws ApplicationException Rethowed framework level exception (should be handled manually)
	 */
	public function run()
	{
		$this->router->mapToRoute($this->router->getRequestPath(), $this->request);

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

		/* Execute an action of some controller */
		try
		{
			$controllerInstance = $this->getControllerInstance($controllerName);

			$response = $this->execute($controllerInstance, $actionName);
			$response->sendHeaders();

			if ($response instanceof Renderable)
			{
				$applicationOutput = $this->render($controllerName, $actionName, $response);

				/* using laypout defined in a controller for action output */
				if ($controllerInstance->getLayout() != null)
				{
					$structure = $controllerInstance->getLayoutStructure();
					foreach($structure as $block)
					{
						if ($block['response'] instanceof BlockResponse)
						{
							$blockOutput = $this->getRenderer()->process($block['response'], $block['view']);
							$this->getRenderer()->appendValue($block['container'], $blockOutput);
						}
						else
						{
							throw new ApplicationException("Unknown response flom a block");
						}
					}
					$this->getRenderer()->setValue("ACTION_VIEW", $applicationOutput);
					echo $this->getRenderer()->render($this->getLayoutPath($controllerInstance->getLayout()));
					/* end layout renderer block */
				}
				else
				{
					echo $applicationOutput;
				}
			}
			else
			{
				echo $response->getData();
			}
		}
		catch(ApplicationException $ex)
		{
			throw $ex;
		}
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
		$controllerPath = implode(".", $controllerPath);

		ClassLoader::import("application.controller.".$controllerPath);
		$controllerSystemPath = ClassLoader::getRealPath("application.controller.".$controllerPath).".php";

		//if (!empty($controllerName) && class_exists($className)) {
		if (file_exists($controllerSystemPath))
		{
			return new $className($this->getRequest());
		}
		else
		{
			throw new ControllerNotFoundException($controllerName);
		}
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
				$paramList = array("controller" => $response->getControllerName(), "action" => $response->getActionName());
				$paramList = array_merge($paramList, $response->getParamList());
				$response->setRedirectURL($this->router->createURL($paramList));
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
				$requestedActionList = $response->getRequestedActionList();
				foreach($requestedActionList as $outputHandle => $location)
				{
					$controllerName = $location[CompositeResponse::CONTROLLER_HANDLE];
					$actionName = $location[CompositeResponse::ACTION_HANDLE];
					$response->setValue($outputHandle, $this->render($controllerName, $actionName, $this->execute($controllerName, $actionName)));
				}
			}
			catch(ApplicationException $ex)
			{
				throw $ex;
			}
		}
	}

	/**
	 * Renders response from controller action
	 *
	 * @param string $controllerName Controller name
	 * @param string $actionName Action name
	 * @param Response $response Response to render
	 * @return string Renderer content
	 * @throws ViewNotFoundException if view does not exists for specified controller
	 */
	public function render($controllerName, $actionName, Response $response)
	{
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
		$controllerPath = explode(".", $controllerName);
		$controllerName = implode(DIRECTORY_SEPARATOR, $controllerPath);

		return implode(DIRECTORY_SEPARATOR, array(ClassLoader::getBaseDir(), 
                                              'application', 
                                              'view', 
                                              $controllerName, 
                                              "$actionName.tpl"));
	}

	/**
	 * Gets a physical layout template path
	 *
	 * @param string $layout layout handle (filename without extension)
	 * @return string
	 */
	public function getLayoutPath($layout)
	{
		return implode(DIRECTORY_SEPARATOR, array(ClassLoader::getBaseDir(), 
												  'application', 
												  'view', 
												  'layout', 
												  "$layout.tpl"));
	}
}
?>