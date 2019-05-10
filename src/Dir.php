<?php namespace Intellex\Filesystem;

use Intellex\Filesystem\Exception\InvalidArgumentException;
use Intellex\Filesystem\Exception\NotADirectoryException;
use Intellex\Filesystem\Exception\PathExistsException;
use Intellex\Filesystem\Exception\PathNotReadableException;
use Intellex\Filesystem\Exception\PathNotWritableException;

/**
 * Class Dir represents a directory on the filesystem.
 *
 * @package Intellex\Filesystem
 */
class Dir extends Path {

	/** @inheritdoc */
	public static function realPath($path) {
		return parent::realPath($path) . static::DS;
	}

	/**
	 * Check if this directory is filesystem root.
	 *
	 * @return bool True if this directory is the filesystem root.
	 */
	public function isRoot() {

		// Handle both Windows or Unix based
		return stristr(PHP_OS, 'WIN')
			? preg_match('~^[A-Z]+:\$~', $this->getPath())
			: $this->getPath() === static::DS;
	}

	/**
	 * Get the paths in this directory.
	 *
	 * @param string $globPattern Pattern for glob search.
	 *
	 * @return Path[] The found paths, both files and directories.
	 * @throws NotADirectoryException
	 * @throws PathNotReadableException
	 */
	public function listDirectory($globPattern = '*') {
		$this->assertIsDir();
		$paths = [];

		// Make sure we can list it
		if (!$this->isReadable()) {
			throw new PathNotReadableException($this);
		}

		// Find in this directory
		$glob = glob($this->getPath() . $globPattern, GLOB_BRACE);
		foreach ($glob as $i => $path) {

			// Handle both files and directories
			if (is_file($path)) {
				$paths[] = new File($path);

			} else {
				$paths[] = $dir = new Dir($path);
			}
		}

		return $paths;
	}

	/**
	 * Perform a recursive glob search in the directory.
	 *
	 * @param string $globPattern Pattern for glob search.
	 *
	 * @return Path[] The found paths, both files and directories.
	 * @throws NotADirectoryException
	 * @throws PathNotReadableException
	 */
	public function findRecursive($globPattern = '*') {
		$paths = $this->listDirectory($globPattern);

		// Get subdirectories
		$children = $this->listDirectory();
		foreach ($children as $child) {
			if ($child instanceof Dir) {
				$paths = array_merge($paths, $child->findRecursive($globPattern));
			}
		}

		return $paths;
	}

	/**
	 * Check if the directory exists on the system.
	 *
	 * @return bool True if found on filesystem.
	 * @throws NotADirectoryException
	 */
	public function exists() {
		$this->assertIsDir();
		return is_dir($this->getPath());
	}

	/**
	 * Check if the directory is readable.
	 *
	 * @return bool True if path is readable.
	 * @throws NotADirectoryException
	 */
	public function isReadable() {
		$this->assertIsDir();
		return is_readable($this->getPath());
	}

	/**
	 * Check if the directory is writable.
	 *
	 * @return bool True if path is writable.
	 * @throws NotADirectoryException
	 */
	public function isWritable() {
		$this->assertIsDir();

		// Check all
		$dir = $this;
		while (true) {

			// Check the permissions on the first existing directory
			if ($dir->exists()) {
				return is_writable($dir->getPath());
			}

			// Filesystem root, no need to further
			if ($dir->isRoot()) {
				break;
			}

			$dir = $dir->getParent();
		}
		return false;
	}

	/**
	 * Touch the directory, or create it if not exists.
	 *
	 * @return Dir Itself, for chaining purposes.
	 * @throws NotADirectoryException
	 * @throws PathNotWritableException
	 */
	public function touch() {
		if (!$this->exists()) {

			// Validate that it is writable
			if (!$this->isWritable()) {
				throw new PathNotWritableException($this);
			}

			// Create a directory
			$success = mkdir($this->getPath(), 0775, true);
			if (!$success) {
				new PathNotWritableException($this->getPath());
			}
		}

		return $this;
	}

	/**
	 * Delete the directory.
	 *
	 * @throws PathNotWritableException
	 * @throws NotADirectoryException
	 * @throws PathNotReadableException
	 */
	public function delete() {
		$this->assertIsDir();

		// Validate that directory exists
		if (!$this->exists()) {
			throw new NotADirectoryException($this);
		}

		// Validate that it is writable
		if (!$this->isWritable()) {
			throw new PathNotWritableException($this);
		}

		$this->clear();
		rmdir($this->getPath());
	}

	/**
	 * Clears the directory content, but not the directory itself.
	 *
	 * @param string[] $exclude The array of regular expressions to match files or dirs to skip.
	 *
	 * @throws NotADirectoryException
	 * @throws PathNotReadableException
	 */
	public function clear($exclude = []) {
		$paths = $this->listDirectory();
		foreach ($paths as $path) {

			// Exclude
			foreach ($exclude as $regexp) {
				if (preg_match($regexp, $path->getName())) {
					break;
				}
			}

			$path->delete();
		}
	}

	/**
	 * Move to a directory.
	 *
	 * @param Path $path      The path to write to the directory.
	 * @param bool $overwrite True to overwrite the existing file, false to throw exception.
	 *
	 * @return Path Itself, for chaining purposes.
	 * @throws InvalidArgumentException When something other than File or Dir is supplied.
	 * @throws PathExistsException If the destination exists and we do not want to overwrite it.
	 * @throws PathNotWritableException If the directory is not writable.
	 * @throws NotADirectoryException
	 */
	public function write(Path &$path, $overwrite = true) {

		// Validate that it is writable
		if (!$this->isWritable()) {
			throw new PathNotWritableException($this);
		}

		// Files
		if ($path instanceof File) {
			$this->touch();

			// Overwrite or not
			$destination = $this->getPath() . static::DS . $path->getName();
			if (!$overwrite && file_exists($destination)) {
				throw new PathExistsException($destination);
			}

			// Execute copy and reinitialize the path
			copy($path->getPath(), $this->getPath() . static::DS . $path->getName());
			$path->init($destination);

			return $this;
		}

		// Directories
		if ($path instanceof Dir) {
			// TODO

			return $this;
		}

		// Error, if reached
		throw new InvalidArgumentException("Only File and Dir objects can to written to directory.");
	}
}
