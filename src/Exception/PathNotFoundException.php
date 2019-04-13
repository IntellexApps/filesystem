<?php namespace Intellex\Filesystem\Exception;

/**
 * Class PathNotFoundException indicates that the path does not exist on the current filesystem.
 *
 * @package Intellex\Filesystem\Exception
 */
class PathNotFoundException extends FilesystemException {

	public function __construct($path) {
		$path = static::getPath($path);
		parent::__construct("The supplied path `{$path}` was not found on the filesystem.");
	}

}
