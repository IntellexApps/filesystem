<?php namespace Intellex\Filesystem;

use Intellex\Filesystem\Exception\NotADirectoryException;
use Intellex\Filesystem\Exception\NotAFileException;
use Intellex\Filesystem\Exception\PathExistsException;
use Intellex\Filesystem\Exception\PathNotReadableException;
use Intellex\Filesystem\Exception\PathNotWritableException;
use Mimey\MimeTypes;

/**
 * Class File represents a file on the filesystem.
 *
 * @package Intellex\Filesystem
 */
class File extends Path {

	/** @var int The full name of the file, with the extension. */
	private $basename;

	/** @var int The name of the file, without the extension. */
	private $filename;

	/** @var int The file size, in bytes. */
	private $size;

	/** @var string|null The extension. */
	private $extension;

	/** @var string MIME type of the file. */
	private $mimetype;

	/** @var string The extension as parsed from the mime type of a file. */
	private $mimeExtension;

	/** @inheritdoc */
	public function init($path) {
		parent::init($path);

		// Clear cached values
		$this->basename = null;
		$this->filename = null;
		$this->size = null;
		$this->extension = null;
		$this->mimetype = null;
		$this->mimeExtension = null;
	}

	/**
	 * Read from the file.
	 *
	 * @return mixed The content of the file.
	 * @throws NotAFileException
	 * @throws PathNotReadableException
	 */
	public function read() {

		// Make sure it is readable
		if (!$this->isReadable()) {
			throw new PathNotReadableException($this);
		}

		return file_get_contents($this->getPath());
	}

	/**
	 * Write arbitrary data to the file.
	 *
	 * @param mixed $data   The data to write.
	 * @param bool  $append True to append to existing data, false to overwrite.
	 *
	 * @throws PathNotWritableException
	 * @throws NotAFileException
	 * @throws NotADirectoryException
	 */
	private function putContents($data, $append) {
		$this->touch();
		if (!$this->isWritable()) {
			throw new PathNotWritableException($this);
		}

		file_put_contents($this->getPath(), $data, $append ? FILE_APPEND : 0);
	}

	/**
	 * Write and overwrite data to the file.
	 *
	 * @param mixed $data The data to write.
	 *
	 * @throws PathNotWritableException
	 * @throws NotAFileException
	 * @throws NotADirectoryException
	 */
	public function write($data) {
		$this->putContents($data, false);
	}

	/**
	 * Append data to the file.
	 *
	 * @param mixed $data The data to write.
	 *
	 * @throws PathNotWritableException
	 * @throws NotAFileException
	 * @throws NotADirectoryException
	 */
	public function append($data) {
		$this->putContents($data, true);
	}

	/**
	 * Copy to another path.
	 *
	 * @param File|string $destination The destination directory.
	 * @param bool        $overwrite   True the existing file.
	 *
	 * @return Path Itself, for chaining purposes.
	 * @throws NotAFileException
	 * @throws PathExistsException
	 * @throws PathNotReadableException
	 * @throws PathNotWritableException
	 * @throws NotADirectoryException
	 */
	public function copyTo($destination, $overwrite = false) {
		if (is_string($destination)) {
			$destination = new File($destination);
		} else if ($destination instanceof Dir) {
			$destination = new File($destination . static::DS . $this->getName());
		}

		// If source file does not exists
		if (!$this->exists()) {
			throw new NotAFileException($this);
		}

		// If destination file exists
		if ($destination->exists() && !$overwrite) {
			throw new PathExistsException($destination);
		}

		// Validate source
		if (!$this->isReadable()) {
			throw new PathNotReadableException($this);
		}

		// Validate destination
		$destination->getParent()->touch();
		if (!$destination->isWritable()) {
			throw new PathNotWritableException($destination);
		}

		// Write
		$destination->write($this->read());

		// Reinitialize the file
		$this->init($destination->getPath());
		return $this;
	}

	/**
	 * Move to another path.
	 *
	 * @param File|string $destination The destination directory.
	 *
	 * @return Path Itself, for chaining purposes.
	 * @throws NotAFileException
	 * @throws PathExistsException
	 * @throws PathNotReadableException
	 * @throws PathNotWritableException
	 * @throws NotADirectoryException
	 */
	public function moveTo($destination) {
		$this->assertIsFile();
		if (is_string($destination)) {
			$destination = new File($destination);
		} else if ($destination instanceof Dir) {
			$destination = new File($destination . static::DS . $this->getName());
		}

		// If source file does not exists
		if (!$this->exists()) {
			throw new NotAFileException($this);
		}

		// If destination file exists
		if ($destination->exists()) {
			throw new PathExistsException($destination);
		}

		// Validate source
		if (!$this->isReadable()) {
			throw new PathNotReadableException($this);
		}
		if (!$this->isWritable()) {
			throw new PathNotWritableException($this);
		}

		// Validate destination
		$destination->getParent()->touch();
		if (!$destination->isWritable()) {
			throw new PathNotWritableException($destination);
		}

		// Move the file
		rename($this->getPath(), $destination->getPath());

		// Reinitialize the file
		$this->init($destination->getPath());
		return $this;
	}

	/**
	 * Initialize the file info.
	 *
	 * @return $this
	 */
	private function load() {

		// Load only once
		if ($this->basename === null || $this->filename === null || $this->extension === null) {
			$info = pathinfo($this->getPath());
			$this->basename = $info['basename'];
			$this->filename = $info['filename'];
			$this->extension = key_exists('extension', $info) ? $info['extension'] : null;
		}

		// Only load additional info for existing files
		try {
			if ($this->exists() && ($this->mimetype === null || $this->mimeExtension === null || empty($this->size))) {
				clearstatcache();
				$this->size = filesize($this->getPath());
				$this->mimetype = $this->parseMimeType();
				$this->mimeExtension = static::validateMimeExtension($this->mimetype, $this->extension);
			}
		} catch (\Exception $ex) {
		}

		return $this;
	}

	/**
	 * Parse the mime type, either using internal
	 *
	 * @return string The recovered mime type.
	 */
	private function parseMimeType() {
		$path = $this->getPath();

		// User internal PHP function
		if (function_exists('mime_content_type')) {
			/** @noinspection PhpComposerExtensionStubsInspection */
			return mime_content_type($path);
		}

		// Use the extension
		$mimeTypes = new MimeTypes();
		return $mimeTypes->getMimeType($path);
	}

	/**
	 * Make sure that the MIME type checked did not faulty concluded a different extension.
	 *
	 * @param string $mimeType  The MIME type of the file.
	 * @param string $extension The named extension of the file.
	 *
	 * @return string The extension to use.
	 */
	public static function validateMimeExtension($mimeType, $extension) {

		// Get the mime extension
		$mimeExtension = (new MimeTypes)->getExtension($mimeType);

		// Exceptions
		$exceptions = [
			'svg' => [ 'html' ]
		];

		// Allow the list in exceptions
		$ext = strtolower($extension);
		if (key_exists($ext, $exceptions)) {

			// If exception is found, return the original extension
			if (in_array(strtolower($mimeExtension), $exceptions[$ext])) {
				return $extension;
			}
		}

		// Return the found MIME extension
		return $mimeExtension;
	}

	/**
	 * Get the name of the file, including extension.
	 *
	 * @return string The name of the path, including extension.
	 */
	public function getName() {
		return $this->load()->basename;
	}

	/** @return string The name of the file, without the extension. */
	public function getFilename() {
		return $this->load()->filename;
	}

	/** @return int The file size, in bytes. */
	public function getSize() {
		return $this->load()->size;
	}

	/**
	 * Get the extension for the file.
	 *
	 * @param bool $fromMimeType False to get extension from filename, true to try to parse it from
	 *                           the detected mime type.
	 *
	 * @return string|null The extension.
	 */
	public function getExtension($fromMimeType = false) {
		$var = $fromMimeType ? 'mimeExtension' : 'extension';
		return $this->load()->$var;
	}

	/** @return string Mimetype of the file. */
	public function getMimetype() {
		return $this->load()->mimetype;
	}

	/** @return int|null The time the file was last modified, in seconds since Unix epoch, or null on failure. */
	public function getLastModifiedTime() {
		$time = filemtime($this->getPath());
		return $time ? $time : null;
	}

	/**
	 * Check if already exists on the system.
	 *
	 * @return bool True if found on filesystem.
	 * @throws NotAFileException
	 */
	public function exists() {
		$this->assertIsFile();
		return is_file($this->getPath());
	}

	/**
	 * Check if is readable.
	 *
	 * @return bool True if path is readable.
	 * @throws NotAFileException
	 */
	public function isReadable() {
		$this->assertIsFile();
		return is_readable($this->getPath());
	}

	/**
	 * Check if is writable, or can be created.
	 *
	 * @return bool True if path is readable.
	 * @throws NotAFileException
	 * @throws NotADirectoryException
	 */
	public function isWritable() {
		$this->assertIsFile();
		return is_writable($this->getPath()) || (!$this->exists() && $this->getParent()->isWritable());
	}

	/**
	 * Touch the file, or create empty it if not exists.
	 *
	 * @return File Itself, for chaining purposes.
	 * @throws PathNotWritableException
	 * @throws NotAFileException
	 * @throws NotADirectoryException
	 */
	public function touch() {
		$this->assertIsFile();

		// Validate that it is writable
		if (!$this->isWritable()) {
			throw new PathNotWritableException($this);
		}

		try {
			$this->getParent()->touch();
		} catch (NotADirectoryException $ex) {
		}
		touch($this->getPath());
		return $this;
	}

	/**
	 * Delete the file from file system.
	 *
	 * @throws NotAFileException
	 * @throws PathNotWritableException
	 * @throws NotADirectoryException
	 */
	public function delete() {
		if ($this->exists() && $this->isWritable()) {

			// Try to skip the error log on failure
			$errorLevel = error_reporting();
			error_reporting($errorLevel & ~E_WARNING);
			@unlink($this->getPath());
			error_reporting($errorLevel);

		} else {
			throw new PathNotWritableException($this);
		}
	}

}
