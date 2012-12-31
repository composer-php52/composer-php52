<?php
/*
 * Copyright (c) 2012, Christoph Mewes, http://www.xrstf.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 *
 * --------------------------------------------------------------------------
 *
 * 99% of this is copied as-is from the original Composer source code and is
 * released under MIT license as well. Copyright goes to:
 *
 * - Fabien Potencier <fabien@symfony.com>
 * - Jordi Boggiano <j.boggiano@seld.be>
 */

class xrstf_Composer52_ClassLoader {
	private $prefixes       = array();
	private $fallbackDirs   = array();
	private $useIncludePath = false;
	private $classMap       = array();

	/**
	 * @return array
	 */
	public function getPrefixes() {
		return $this->prefixes;
	}

	/**
	 * @return array
	 */
	public function getFallbackDirs() {
		return $this->fallbackDirs;
	}

	/**
	 * @return array
	 */
	public function getClassMap() {
		return $this->classMap;
	}

	/**
	 * @param array $classMap  class to filename map
	 */
	public function addClassMap(array $classMap) {
		if ($this->classMap) {
			$this->classMap = array_merge($this->classMap, $classMap);
		}
		else {
			$this->classMap = $classMap;
		}
	}

	/**
	 * Registers a set of classes
	 *
	 * @param string       $prefix  the classes prefix
	 * @param array|string $paths   the location(s) of the classes
	 */
	public function add($prefix, $paths) {
		$paths = (array) $paths;

		if (!$prefix) {
			foreach ($paths as $path) {
				$this->fallbackDirs[] = $path;
			}

			return;
		}

		if (isset($this->prefixes[$prefix])) {
			$this->prefixes[$prefix] = array_merge($this->prefixes[$prefix], $paths);
		}
		else {
			$this->prefixes[$prefix] = $paths;
		}
	}

	/**
	 * Turns on searching the include path for class files.
	 *
	 * @param bool $useIncludePath
	 */
	public function setUseIncludePath($useIncludePath) {
		$this->useIncludePath = $useIncludePath;
	}

	/**
	 * Can be used to check if the autoloader uses the include path to check
	 * for classes.
	 *
	 * @return bool
	 */
	public function getUseIncludePath() {
		return $this->useIncludePath;
	}

	/**
	 * Registers this instance as an autoloader.
	 */
	public function register() {
		spl_autoload_register(array($this, 'loadClass'), true);
	}

	/**
	 * Unregisters this instance as an autoloader.
	 */
	public function unregister() {
		spl_autoload_unregister(array($this, 'loadClass'));
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param  string $class  the name of the class
	 * @return bool|null      true, if loaded
	 */
	public function loadClass($class) {
		if ($file = $this->findFile($class)) {
			include $file;
			return true;
		}
	}

	/**
	 * Finds the path to the file where the class is defined.
	 *
	 * @param  string $class  the name of the class
	 * @return string|null    the path, if found
	 */
	public function findFile($class) {
		if ('\\' === $class[0]) {
			$class = substr($class, 1);
		}

		if (isset($this->classMap[$class])) {
			return $this->classMap[$class];
		}

		$classPath = self::getClassPath($class);

		foreach ($this->prefixes as $prefix => $dirs) {
			if (0 === strpos($class, $prefix)) {
				foreach ($dirs as $dir) {
					if (file_exists($dir.DIRECTORY_SEPARATOR.$classPath)) {
						return $dir.DIRECTORY_SEPARATOR.$classPath;
					}
				}
			}
		}

		foreach ($this->fallbackDirs as $dir) {
			if (file_exists($dir.DIRECTORY_SEPARATOR.$classPath)) {
				return $dir.DIRECTORY_SEPARATOR.$classPath;
			}
		}

		if ($this->useIncludePath && $file = self::resolveIncludePath($classPath)) {
			return $file;
		}

		return $this->classMap[$class] = false;
	}

	private static function getClassPath($class) {
		if (false !== $pos = strrpos($class, '\\')) {
			// namespaced class name
			$classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)).DIRECTORY_SEPARATOR;
			$className = substr($class, $pos + 1);
		}
		else {
			// PEAR-like class name
			$classPath = null;
			$className = $class;
		}

		$classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';

		return $classPath;
	}

	public static function resolveIncludePath($classPath) {
		$paths = explode(PATH_SEPARATOR, get_include_path());

		foreach ($paths as $path) {
			$path = rtrim($path, '/\\');

			if ($file = file_exists($path.DIRECTORY_SEPARATOR.$file)) {
				return $file;
			}
		}

		return false;
	}
}
