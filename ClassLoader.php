<?php

//require_once("ClassLoaderException.php");
//ClassLoader::import("framework.ClassLoaderException");

/**
 * Automatically loads a requested class
 *
 * A "magic" PHP5 specific function that is called automatically in case you are trying
 * to use a class which hasn't been defined yet.
 *
 * A function tries to include file "ClassName.php" where "ClassName" is a requested class
 *
 * @param string $className Class Name
 * @link  http://www.php.net/__autoload
 */
function __autoload($className)
{
	ClassLoader::load($className);
}

/**
 * Application class loader
 *
 * @package	framework
 * @author Saulius Rupainis <saulius@integry.net>
 */
class ClassLoader
{
	/**
	 * Mount point list
	 *
	 * Mount point is kind of a path handle: it gives a shorter name for a system path
	 * (directory) which can be used in an application
	 *
	 * @see self::mountPath()
	 * @see self::unmountPath()
	 * @var array
	 */
	private static $mountList = array();

	/**
	 * Reserve mount point list
	 *
	 * Provides ability to specify multiple file paths under the same mount path. If the main path
	 * is not found, reserve paths are checked. This allows to set multiple directories for controllers,
	 * views, models, etc.
	 *
	 * @see self::mountReservePath()
	 * @see self::mountPath()
	 * @see self::unmountPath()
	 * @var array
	 */
	private static $reserveMountList = array();

	private static $autoLoadFunctions = array();

	/**
	 * Loads a class file (performs include_once)
	 *
	 * @param string $class Class file
	 */
	public static function load($class)
	{
		preg_match('/([^\\' . DIRECTORY_SEPARATOR . ']+)$/', $class, $matches); //substr($class, strrpos($class, DIRECTORY_SEPARATOR));
		$className = $matches[1];
	
		// try custom autoload functions
		foreach (self::$autoLoadFunctions as $func => $isEnabled)
		{
			if ($func($class))
			{
				return true;
			}
		}
		
		if (!class_exists($className, false))
		{
			if(!(include_once($class.'.php')))
			{
				throw new ClassLoaderException('File '.$class.'.php not found');
			}
		}
	}

	public static function registerAutoLoadFunction($functionName)
	{
		self::$autoLoadFunctions[$functionName] = true;
	}

	/**
	 * Registers a new mount point
	 *
	 * @param string $mountName
	 * @param string $fullDirPath
	 */
	public static function mountPath($mountName, $fullDirPath)
	{
	    $fullDirPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $fullDirPath);
		if (file_exists($fullDirPath))
		{
			self::$mountList[$mountName] = $fullDirPath;
		}
		else
		{
		    throw new ClassLoaderException("No such directory: $fullDirPath");
		}
	}

	/**
	 * Registers a new reserve path. Multiple reserve paths can be used for each mount point.
	 *
	 * @param string $mountName
	 * @param string $fullDirPath
	 */
	public static function mountReservePath($mountName, $fullDirPath)
	{
		if (is_dir($fullDirPath))
		{
			self::$reserveMountList[$mountName][] = $fullDirPath;
		}
		else
		{
			throw new ClassLoaderException("No such directory: $fullDirPath");
		}
	}
	
	/**
	 * Removes a mount point
	 *
	 * @param string $mountName
	 */
	public static function unmountPath($mountName)
	{
		unset(self::$mountList[$mountName]);
		unset(self::$reserveMountList[$mountName]);
	}

	/**
	 * Appends an include_path which is used to look for a requested class files or includes
	 * a class file (depends on suplied string form)
	 *
	 * Directories and files in a path ($space) are separated by dots: somedir.subdir.3rdleveldir
	 *
	 * To include the whole directory (package) use a "wildcard" identifier:
	 * <code>
	 * ClassLoader::import('site.somedir.someother.*');
	 * </code>
	 *
	 * this will add [app-dir]/site/somedir/someother/ directory to an include_path. No physical
	 * inclusion would be performed by this call.
	 *
	 * To include a concrete class file use:
	 * <code>
	 * ClassLoader::import('site.libs.SuperLib');
	 * </code>
	 * This will perform require_once with a parameter '[app-dir]/site/libs/SuperLib.php'
	 *
	 * @param string $path
	 */
	public static function import($path)
	{
		$path = self::getRealPath($path);

		if (strpos($path, '*'))
		{
			$path = str_replace('*', '', $path);
			self::importPath($path);
		}
		else
		{
			self::load($path);
		}
	}

	/**
	 * Translates a path to a system path by looking at a mount list
	 *
	 * @param unknown_type $path
	 * @return unknown
	 */
	private static function mapToMountPoint($path)
	{
		$possiblePoints = array();
		$parts = $pathParts = explode(".", $path);
		
		$processed = array();
		foreach ($pathParts as $part)
		{
			$processed[] = $part;
			$possiblePoints[implode('.', $processed)] = true;
		}
		
		$res = array_intersect_key(self::$mountList, $possiblePoints);
	
		if ($res)
		{
			end($res);
			$found = key($res);
			$pathParts = array_slice($pathParts, count(explode('.', $found)));
			$mountedPath = $res[$found] . ($pathParts ? '.' : '');
		}
		else
		{
			if (!empty($pathParts[0]))
			{
				if (!empty(self::$mountList[$pathParts[0]]))
				{
					$mountedPath = self::$mountList[$pathParts[0]];
					unset($pathParts[0]);
				}
				else if (!empty(self::$mountList["."]))
				{
					$mountedPath = self::$mountList["."];
				}
			}
		}

		$mountPoints[] = $mountedPath . implode(".", $pathParts);

		$reserve = array_intersect_key(self::$reserveMountList, $possiblePoints);
		if ($reserve)
		{
			end($reserve);
			$found = key($reserve);
			$reserveParts = array_slice($parts, count(explode('.', $found)));
			foreach ($reserve[$found] as $reservePath)
			{
				$mountPoints[] = $reservePath . ($reserveParts ? '.' . implode(".", $reserveParts) : '');
			}			
		}
		
		return $mountPoints;
	}	

	/**
	 * Removes a path from an include_path
	 *
	 * Example:
	 * <code>
	 * ClassLoader::remove("site.somedir.someother.*");
	 * </code>
	 *
	 * @param string $path
	 * @param string $root Root path
	 */
	public static function remove($path)
	{
		self::removePath(str_replace('*', '', self::getRealPath($path)));
	}

	/**
	 * Gets framework filebase
	 *
	 * @return string base directory
	 */
	public static function getBaseDir()
	{
        if (!empty(self::$mountList["."]))
		{
			return self::$mountList["."];
		}
	}

	/**
	 * Gets a translated (physical) path by using a package path
	 *
	 * @param string $path
	 * @return string
	 */
	public static function getRealPath($path)
	{
		$mounted = self::mapToMountPoint($path); 
        
		foreach ($mounted as $path)
		{
			$path = str_replace('.', DIRECTORY_SEPARATOR, $path);
			$replaced = str_replace('*', '', $path);
			if (is_file($replaced . '.php') || is_dir($replaced))
			{
				return $path;
			}
		}
		
		return str_replace('.', DIRECTORY_SEPARATOR, $mounted[0]);			
	}

	/**
	 * Sets framework filebase
	 *
	 * @param string $dir Directory name
	 */
	public static function setBaseDir($dir)
	{
		self::mountPath(".", $dir);
	}

	/**
	 * Modifies php include_path
	 *
	 * @param string $path Directory
	 * @param boolean $check Chech if directory exists
	 */
	public static function importPath($path, $check = true)
	{
		if (substr($path, -1, 1) == DIRECTORY_SEPARATOR)
		{
			$path = substr($path, 0, strlen($path) - 1);
		}
		$oldPath = get_include_path();
		if ($check)
		{
			$oldPathArray = explode(PATH_SEPARATOR, $oldPath);
			if (!is_dir($path))
			{
				return false;
			}
			if (in_array($path, $oldPathArray))
			{
				return false;
			}
		}
		ini_set('include_path', $path.PATH_SEPARATOR.$oldPath);
	}

	/**
	 * Removes path from php include_path
	 *
	 * @param string $path Directory
	 */
	private static function removePath($path)
	{
		$oldPathArray = explode(PATH_SEPARATOR, get_include_path());
		$oldPathCount = count($oldPathArray);

		$oldPath = array_diff($oldPathArray, array($path));
		if (count($oldPath) < $oldPathCount)
		{
			ini_set('include_path', implode(PATH_SEPARATOR, $oldPath));
		}
	}
}

?>