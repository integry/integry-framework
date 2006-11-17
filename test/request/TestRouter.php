<?php

require_once("../../ClassLoader.php");
ClassLoader::import("framework.request.Router");

$router = Router::getInstance();
$request = new Request();

$router->connect(":controller", array("action" => "index"));
$router->connect(":controller/:action");
$router->connect(":controller/:action/:id", array(), array("id" => "[0-9]+"));
$router->connect(":controller/:action/:mode/:id", array(), array("id" => "[0-9]+", "mode" => "create|modify"));
$router->connect(":controller/:action/:language", array(), array("language" => "\w{2}"));

$mappedRoute = $router->mapToRoute("hi", $request);
//echo "\n<pre>"; print_r($request); echo "</pre>\n";

echo $router->createURL(array("controller" => "backend.category", "action" => "index")); echo "\n";
echo $router->createURL(array("controller" => "filter", "action" => "modify", "id" => "2231",  "query" => "language=en")); echo "\n";
echo $router->createURL(array("controller" => "index", "query" => "language=en")); echo "\n";
echo $router->createURL(array("controller" => "backend.language", "action" => "update", "language" => "eng"));
//echo "\n<pre>"; print_r($router); echo "</pre>\n";

?>