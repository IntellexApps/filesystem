<?php namespace Intellex\Filesystem;

use Intellex\Filesystem\Exception\NotADirectoryException;
use Intellex\Filesystem\Exception\NotAFileException;
use Intellex\Filesystem\Exception\UnsupportedOSException;

/**
 * Class Path represents a path, which can be both Dir or File, or not even existing yet.
 *
 * @package Intellex\Filesystem
 */
abstract class Path {

	/** @var string The absolute path to the file or directory. */
	private $path;

	/** const string  */
	const DS = DIRECTORY_SEPARATOR;

	/** @var string Indicates that the underlying OS is UNIX based. */
	const OS_UNIX = 'UNIX';

	/** @var string Indicates that the underlying OS is Windows based. */
	const OS_WIN = 'WIN';

	/**
	 * Get the underlying operating system.
	 *
	 * @return string Either static::OS_UNIX or static::OS_WIN.
	 */
	public static function getOS() {
		if (stristr(PHP_OS, 'WIN') !== false) {
			return static::OS_WIN;
		}

		// Default to UNIX
		if (true) {
			return static::OS_UNIX;
		}

		throw new UnsupportedOSException(PHP_OS);
	}

	/**
	 * Get the root directory for the filesystem.
	 * In case of filesystems with multiple roots, the root where this file is located will be used.
	 *
	 * @return Dir The root Dir.
	 */
	public static function getRoot() {
		$root = '';
		$file = __FILE__;
		$length = strlen($file);

		// Read the path of this file until the first directory separator character.
		for ($i = 0; $i < $length; $i++) {
			$root .= $file[$i];
			if ($file[$i] === static::DS) {
				return new Dir($root);
			}
		}

		return null;
	}

	/**
	 * Initialize file or directory.
	 *
	 * @param string|string[] $path The path to the file or directory.
	 */
	public function __construct($path) {
		$this->init($path);
	}

	/**
	 * Initialize the path.
	 *
	 * @param string|string[] $path The path to the file or directory.
	 */
	protected function init($path) {
		$this->path = static::realPath($path);
	}

	/**
	 * Get the name of the file or the directory.
	 *
	 * @return string The name, with file extension (if applicable).
	 */
	public function getName() {
		$info = pathinfo($this->getPath());
		return $info['basename'];
	}

	/**
	 * Get the path to the file or directory.
	 *
	 * @return string The path to the file or directory, always with trailing slash.
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Get the parent directory.
	 *
	 * @return Dir|null The parent directory, or null if called on the filesystem root.
	 */
	public function getParent() {
		if ($this instanceof Dir && $this->isRoot()) {
			return null;
		}

		return new Dir(dirname($this->path));
	}

	/**
	 * Check if this path is absolute or relative.
	 *
	 * @param Path|string $path The path to check.
	 *
	 * @return bool True this path is absolute.
	 */
	public static function isAbsolute($path) {
		switch (static::getOS()) {
			case static::OS_UNIX:
				return $path{0} === static::DS;
			case static::OS_WIN:
				return preg_match('~^[A-Z]+:\\\\~', $path . '');
		}

		// Will never reach, as getOS will throw an exception
		return null;
	}

	/**
	 * Get the cleaned up path.
	 *
	 * @param Path|string|string[] $path The to get the real path from.
	 *
	 * @return string The true and absolute path, without trailing slash.
	 */
	public static function realPath($path) {

		// If an array has been submitted
		if (is_array($path)) {
			$path = implode(static::DS, $path);
		}

		// Make it an absolute path
		$path = !static::isAbsolute($path)
			? __DIR__ . static::DS . $path
			: $path;

		// Clear back links
		$path = str_replace(static::DS . '..' . static::DS, '', $path . static::DS);

		// Clean up double DS
		return rtrim(preg_replace('~' . static::DS . static::DS . '+~', DIRECTORY_SEPARATOR, $path), static::DS);
	}

	/**
	 * Make sure that the supplied path is a file.
	 *
	 * @throws NotAFileException
	 */
	public function assertIsFile() {
		if ($this instanceof Dir || (file_exists($this->path) && !is_file($this->path))) {
			throw new NotAFileException($this);
		}
	}

	/**
	 * Make sure that the supplied path is a directory.
	 *
	 * @throws NotADirectoryException
	 */
	public function assertIsDir() {
		if ($this instanceof File || (file_exists($this->path) && !is_dir($this->path))) {
			throw new NotADirectoryException($this);
		}
	}

	/**
	 * Join an array of nodes in a single path.
	 *
	 * @param string[] $path The elements of the path.
	 *
	 * @return string The path joined, without the root and without the trailing slash.
	 */
	public static function join($path) {
		return trim(implode(static::DS, $path), static::DS);
	}

	/**
	 * Join an array of nodes in a single path and append the root.
	 *
	 * @param string[] $path The elements of the path.
	 *
	 * @return string The path joined, with the root, but without the trailing slash.
	 */
	public static function joinAsAbsolute($path) {
		return static::getRoot() . implode(static::DS, $path);
	}

	/** @return string The path. */
	public function __toString() {
		return $this->getPath();
	}

	/**
	 * Check if the path exists on the system.
	 *
	 * @return bool True if found on filesystem.
	 */
	abstract public function exists();

	/**
	 * Check if the path is readable.
	 *
	 * @return bool True if path is readable.
	 */
	abstract public function isReadable();

	/**
	 * Check if the path is writable.
	 *
	 * @return bool True if path is writable.
	 */
	abstract public function isWritable();

	/**
	 * Touch the path, or create if not exists.
	 *
	 * @return Path Itself, for chaining purposes.
	 */
	abstract public function touch();

	/**
	 * Delete the path.
	 */
	abstract public function delete();

}
