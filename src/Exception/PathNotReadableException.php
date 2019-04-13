<?php namespace Intellex\Filesystem\Exception;

/**
 * Class PathNotReadableException indicates that a path is not readable.
 *
 * @package Intellex\Filesystem\Exception
 */
class PathNotReadableException extends FilesystemException {

	public function __construct($path) {
		$path = static::getPath($path);
		parent::__construct("The supplied path `{$path}` is not readable.");
	}

}
