<?php
class Test
{
    
}

/**
 * General exception which might be raised within an application context
 *
 * @package framework
 * @author Saulius Rupainis <saulius@integry.net>
 */
class ApplicationException extends Exception 
{
			
	public static function getFileTrace($trace)
	{
		$showedFiles = array();
		$i = 0;
		$traceString = '';
				
		$ajax = false; //isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false;
		
				
    	// Get new line
    	$newLine = $ajax ? "\n" : "<br />\n";
				
		foreach($trace as $call)
		{
			if(isset($call['file']) && isset($call['line']) && !isset($showedFiles[$call['file']][$call['line']]))
			{
				$showedFiles[$call['file']][$call['line']] = true;
				
				// Get file name and line
				if($ajax) 
				{
				    $position = ($i++).": {$call['file']}:{$call['line']}";
				}
				else 
				{
				    $position = "<strong>".($i++)."</strong>: \"{$call['file']}\":{$call['line']}";
				}
				
				// Get function name
				if(isset($call['class']) && isset($call['type']) && isset($call['function']))
				{
				    $functionName = "{$call['class']}{$call['type']}{$call['function']}";
				} 
				else 
				{
				    $functionName = $call['function'];
				}
				
				// Get function arguments
				$arguments = '';
				$j = 1;
				foreach($call['args'] as $argv) 
				{
				    switch(gettype($argv)) 
				    {
				        case 'string':
				            $arguments .= "\"$argv\"";
			            break;
				        case 'boolean':
				             $arguments .= ($argv ? 'true' : 'false');
			            break;
				        case 'integer':
				        case 'double':
				             $arguments .= $argv;
			            break;
				        case 'object':
				             $arguments .= "(object)" . get_class($argv);
				        break;
				        case 'array':
				             $arguments .= "Array";
			            break;
				        default:
				            $arguments .= $argv;
			            break;
				    }
				    
				    if($j < count($call['args'])) $arguments .= ", "; $j++;
				}

				
				// format the output line
				$traceString .= "$newLine$position - $functionName($arguments)";
			}
		}
		
		return $traceString;
	}
}

?>
