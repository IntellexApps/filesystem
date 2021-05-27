<?php namespace Intellex\Filesystem\Exception;

/**
 * Class PathExistsException indicates that the target path already exists.
 *
 * @package Intellex\Filesystem\Exception
 */
class PathExistsException extends FilesystemException {

	public function __construct($path) {
		$path = static::getPath($path);
		parent::__construct("The supplied path `{$path}` already exists.");
	}

}
