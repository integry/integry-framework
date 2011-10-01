<?php

require_once("ClassLoaderException.php");

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
 * @package framework
 * @author Integry Systems
 */

spl_autoload_register(array('ClassLoader', 'load'));

/**
 * Application class loader
 *
 * @package	framework
 * @author Integry Systems
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

	private static $registeredImports = array();

	private static $importedPaths = array();

	private static $realPathCache = array();

	private static $mountPointCache = array();

	private static $isCacheChanged = false;

	private static $ignoreMissingClasses = false;

	/**
	 * Loads a class file (performs include_once)
	 *
	 * @param string $class Class file
	 */
	public static function load($class)
	{
		if (!empty(self::$registeredImports[strtolower($class)]))
		{
			self::load(self::$registeredImports[strtolower($class)]);
			return;
		}

		preg_match('/([^\\' . DIRECTORY_SEPARATOR . ']+)$/', $class, $matches); //substr($class, strrpos($class, DIRECTORY_SEPARATOR));
		$className = $matches[1];

		// try custom autoload functions
		foreach (self::$autoLoadFunctions as $func => $isEnabled)
		{
			if ($func($class))
			{
				return $className;
			}
		}

		if (!class_exists($className, false))
		{
			if(!(include_once($class.'.php')) && !self::$ignoreMissingClasses)
			{
				// WTF PHP bug
				// #0  ClassLoader::load(g25c7hui)
				// #1  spl_autoload_call(g25c7hui) <<== g25c7hui instead of EavField ?
				// #2  call_user_func(Array ([0] => EavField,[1] => getFieldIDColumnName)) called at ../application/model/eavcommon/EavSpecificationManagerCommon.php:816]
				// also wtf'ed here: http://stackoverflow.com/questions/3517789/scrambled-class-name-passed-to-spl-autoload-call-via-call-user-func
				$hasWtfBug = preg_match('/^[_a-z0-9]+$/', $className) && preg_match('/[0-9]/', $className);

				// for now just ignore as the necessary class is likely to be already loaded
				// but perhaps we'll need to look at debug_backtrace if it isn't
				if ($hasWtfBug)
				{
					return;
				}

				ClassLoader::import("framework.ClassLoaderException");
				throw new ClassLoaderException('File '.$class.'.php not found');
			}
		}

		return $className;
	}

	public static function ignoreMissingClasses($ignore = true)
	{
		self::$ignoreMissingClasses = $ignore;
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
	public static function import($path, $now = false)
	{
		if (isset(self::$importedPaths[$path]) && !$now)
		{
			return array_pop(explode('.', $path));
		}

		self::$importedPaths[$path] = true;

		$realPath = self::getRealPath($path);

		if (!$now)
		{
			self::$registeredImports[strtolower(array_pop(explode('.', $path)))] = $realPath;
		}

		if (strpos($realPath, '*'))
		{
			$realPath = str_replace('*', '', $realPath);
			return self::importPath($realPath);
		}
		else if ($now)
		{
			return self::load($realPath);
		}
	}

	public static function importNow($path)
	{
		self::import($path, true);
	}

	/**
	 * Translates a path to a system path by looking at a mount list
	 *
	 * @param unknown_type $path
	 * @return unknown
	 */
	public static function mapToMountPoint($path)
	{
		if (isset(self::$mountPointCache[$path]))
		{
			return self::$mountPointCache[$path];
		}

		$possiblePoints = array();
		$parts = explode('/', strtr($path, array('.' => '/', '#' => '.')));

		$pathParts = $parts;

		if (count(self::$mountList) > 1)
		{
			$processed = array();
			foreach ($pathParts as $part)
			{
				$processed[] = $part;
				$possiblePoints[implode('.', $processed)] = true;
			}

			$res = array_intersect_key(self::$mountList, $possiblePoints);
		}

		if (!empty($res))
		{
			end($res);
			$found = key($res);
			$pathParts = array_slice($pathParts, count(explode('.', $found)));
			$mountedPath = $res[$found] . ($pathParts ? DIRECTORY_SEPARATOR : '');
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

		if (!isset($mountedPath))
		{
			$mountedPath = self::$mountList["."];
		}

		$mountPoints[] = $mountedPath . implode(DIRECTORY_SEPARATOR, $pathParts);

		if (self::$reserveMountList)
		{
			$reserve = array_intersect_key(self::$reserveMountList, $possiblePoints);
			if ($reserve)
			{
				end($reserve);
				$found = key($reserve);
				$reserveParts = array_slice($parts, count(explode('.', $found)));
				foreach ($reserve[$found] as $reservePath)
				{
					$mountPoints[] = $reservePath . ($reserveParts ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $reserveParts) : '');
				}
			}
		}

		if (self::$mountPointCache)
		{
			self::$isCacheChanged = true;
		}

		self::$mountPointCache[$path] = $mountPoints;

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
		if (!isset(self::$realPathCache[$path]))
		{
			if (self::$realPathCache)
			{
				self::$isCacheChanged = true;
			}

			self::$realPathCache[$path] = null;
			$mounted = self::mapToMountPoint($path);

			foreach ($mounted as $possiblePath)
			{
				$replaced = str_replace('*', '', $possiblePath);
				if (is_file($replaced . '.php') || is_dir($replaced))
				{
					self::$realPathCache[$path] = $possiblePath;
					break;
				}
			}

			if (!self::$realPathCache[$path])
			{
				self::$realPathCache[$path] = array_shift($mounted);
			}
		}

		return self::$realPathCache[$path];
	}

	public static function getRealPathCache()
	{
		return self::$realPathCache;
	}

	public static function setRealPathCache($paths)
	{
		self::$realPathCache = array_merge(self::$realPathCache, $paths);
	}

	public static function getMountPointCache()
	{
		return self::$mountPointCache;
	}

	public static function setMountPointCache($paths)
	{
		self::$mountPointCache = array_merge(self::$mountPointCache, $paths);
	}

	public static function isCacheChanged()
	{
		return self::$isCacheChanged;
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