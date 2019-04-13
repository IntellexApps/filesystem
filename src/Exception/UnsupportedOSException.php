<?php namespace Intellex\Filesystem\Exception;

/**
 * Class UnsupportedOSException indicates that the script was run on an unsupported OS.
 *
 * @package Intellex\Filesystem\Exception
 */
class UnsupportedOSException extends \RuntimeException {

	public function __construct($signature) {
		parent::__construct("Unsupported OS as described with " . $signature);
	}

}
