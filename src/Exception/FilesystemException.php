<?php namespace Intellex\Filesystem\Exception;

use Exception;
use Intellex\Filesystem\Path;

/**
 * Class FilesystemException is the base exception for all file system related exceptions.
 *
 * @package Intellex\Filesystem\Exception
 */
abstract class FilesystemException extends Exception {

	/**
	 * Get the string path.
	 *
	 * @param Path|string $object Either object of Path or a string to the path.
	 *
	 * @return string The requested path.
	 */
	public static function getPath($object) {
		return $object instanceof Path
			? $object->getPath()
			: $object;
	}
}
