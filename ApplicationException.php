<?php

/**
 * General exception which might be raised within an application context
 *
 * @package framework
 * @author Saulius Rupainis <saulius@integry.net>
 */
class ApplicationException extends Exception 
{
			
	public function getFileTrace()
	{
		$showedFiles = array();
		$i = 0;
		foreach($this->getTrace() as $call)
		{
			if(isset($call['file']) && isset($call['line']) && !isset($showedFiles[$call['file']][$call['line']]))
			{
				$showedFiles[$call['file']][$call['line']] = true;
				echo '<strong>'.($i++).'</strong>: '.$call['file'].': '.$call['line'].'<br />';
			}
		}
	}
}

?>
