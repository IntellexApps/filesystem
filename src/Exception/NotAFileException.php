<?php namespace Intellex\Filesystem\Exception;

/**
 * Class NotAFileException indicates that a path should be a file, buy directory was found
 * instead.
 *
 * @package Intellex\Filesystem\Exception
 */
class NotAFileException extends FilesystemException {

	public function __construct($path) {
		$path = static::getPath($path);
		parent::__construct("The supplied path `{$path}` is not a file.");
	}

}
