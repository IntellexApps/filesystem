<?php namespace Intellex\Filesystem\Exception;

/**
 * Class PathNotReadableException indicates that a path is not writable.
 *
 * @package Intellex\Filesystem\Exception
 */
class PathNotWritableException extends FilesystemException {

	public function __construct($path) {
		$path = static::getPath($path);
		parent::__construct("The supplied path `{$path}` is not writable.");
	}

}
