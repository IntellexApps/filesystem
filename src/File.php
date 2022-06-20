<?php namespace Intellex\Filesystem;

use Exception;
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

	/** @var string The full name of the file, with the extension. */
	private $basename;

	/** @var string The name of the file, without the extension. */
	private $filename;

	/** @var int The file size, in bytes. */
	private $size;

	/** @var string|null The extension. */
	private $extension;

	/** @var string MIME type of the file. */
	private $mimeType;

	/** @var string The extension as parsed from the mime type of file. */
	private $mimeExtension;

	/** @inheritdoc */
	public function init($path) {
		parent::init($path);

		// Clear cached values
		$this->basename = null;
		$this->filename = null;
		$this->size = null;
		$this->extension = null;
		$this->mimeType = null;
		$this->mimeExtension = null;
	}

	/**
	 * Read from the file.
	 *
	 * @return string The content of the file.
	 * @throws NotAFileException
	 * @throws PathNotReadableException
	 */
	public function read() {

		// Make sure it is readable
		if (!$this->isReadable()) {
			throw new PathNotReadableException($this);
		}

		// Read the file
		static::disableErrorHanding();
		$fileContent = @file_get_contents($this->getPath());
		static::restoreErrorHanding();

		// Make sure the file is read properly
		if ($fileContent === false) {
			throw new PathNotReadableException($this);
		}

		return $fileContent;
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

		// Write to file
		static::disableErrorHanding();
		$success = @file_put_contents($this->getPath(), $data, $append ? FILE_APPEND : 0);
		static::restoreErrorHanding();

		// Make sure the data is written properly
		if ($success === false) {
			throw new PathNotWritableException($this);
		}
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

		// If source file does not exist
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

		// If source file does not exist
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
			if ($this->exists() && ($this->mimeType === null || $this->mimeExtension === null || empty($this->size))) {
				clearstatcache();
				$this->size = filesize($this->getPath());
				$this->mimeType = $this->parseMimeType();
				$this->mimeExtension = static::validateMimeExtension($this->mimeType, $this->extension);
			}
		} catch (Exception $ex) {
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
			$mime = mime_content_type($path);

			// Fix some known bugs on older versions of PHP: https://bugs.php.net/bug.php?id=79045
			switch ($mime) {
				case 'image/svg':
					$mime = 'image/svg+xml';
					break;
			}

			return $mime;
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

	/** @return string MimeType of the file. */
	public function getMimeType() {
		return $this->load()->mimeType;
	}

	/**
	 * Get the last access time.
	 *
	 * @return int The time the file was last accessed, in seconds since Unix epoch.
	 * @throws PathNotReadableException
	 */
	public function getLastAccessedTime() {
		$time = fileatime($this->getPath());

		// Make sure the data is read
		if ($time === false) {
			throw new PathNotReadableException($this);
		}

		return $time;
	}

	/**
	 * Get the last modified time.
	 *
	 * @return int The time the file was last modified, in seconds since Unix epoch.
	 * @throws PathNotReadableException
	 */
	public function getLastModifiedTime() {
		$time = filemtime($this->getPath());

		// Make sure the data is read
		if ($time === false) {
			throw new PathNotReadableException($this);
		}

		return $time;
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

		// Try to touch the parent directory
		try {
			$this->getParent()->touch();
		} catch (NotADirectoryException $ex) {
		}

		// Touch the file
		static::disableErrorHanding();
		$success = @touch($this->getPath());
		static::restoreErrorHanding();

		// Make sure the touch was successful
		if ($success === false) {
			throw new PathNotWritableException($this);
		}

		return $this;
	}

	/**
	 * Delete the file from file system.
	 *
	 * @throws NotAFileException
	 * @throws NotADirectoryException
	 * @throws PathNotWritableException
	 */
	public function delete() {
		if ($this->exists() && $this->isWritable()) {

			// Delete the file
			static::disableErrorHanding();
			$success = @unlink($this->getPath());
			static::restoreErrorHanding();

			// Make sure to unlink was successful
			if ($success === false) {
				throw new PathNotWritableException($this);
			}

		} else {
			throw new PathNotWritableException($this);
		}
	}

}
