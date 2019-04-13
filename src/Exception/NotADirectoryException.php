<?php namespace Intellex\Filesystem\Exception;

/**
 * Class NotADirectoryException indicates that a path should be a directory, but file was found
 * instead.
 *
 * @package Intellex\Filesystem\Exception
 */
class NotADirectoryException extends FilesystemException {

	public function __construct($path) {
		$path = static::getPath($path);
		parent::__construct("The supplied path `{$path}` is not a directory.");
	}

}
