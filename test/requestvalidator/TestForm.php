<?php

require_once("../../ClassLoader.php");
ClassLoader::import("framework.request.Request");
require_once("../../request/validator/Form.php");
require_once("../../request/validator/check/IsNotEmptyCheck.php");
require_once("../../request/validator/check/MaxLengthCheck.php");


$request = new Request();
$request->setValue("name", "");
$request->setValue("email", "test@example.com");

$validator = new RequestValidator("test", $request);
$validator->addCheck("name", new IsNotEmptyCheck("You must fill in your name"));
$validator->addCheck("name", new MaxLengthCheck("Name should not be longer that 40 chars", 40));
$validator->addCheck("email", new IsNotEmptyCheck("You must fill in your email address"));
$validator->execute();

if ($validator->hasFailed()) 
{
	$validator->saveState();
	echo "State saved!\n";
}

$formData = array("name" => "John", 
				  "email" => "someother@example.com", 
				  "catalogID" => "1");

$form = new Form($validator);
$form->setData($formData);


print_r($form->getValidator()->getJSValidatorParams());

//print_r($form);
//print_r($_SESSION);

?>