<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\file;

use FilesystemIterator;
use serve\utility\Mime;
use SplFileObject;
use function array_diff;
use function array_values;
use function extract;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function filesize;
use function glob;
use function is_dir;
use function is_file;
use function is_readable;
use function is_writable;
use function mkdir;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function rename;
use function rmdir;
use function scandir;
use function tmpfile;
use function touch;
use function unlink;

/**
 * Filesystem helper.
 *
 * @author Joe J. Howard
 */
class Filesystem
{
	/**
	 * Returns TRUE if a file exists and FALSE if not.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public static function exists(string $file): bool
	{
		return file_exists($file);
	}

	/**
	 * Returns TRUE if the provided path is a file and FALSE if not.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public static function isFile(string $file): bool
	{
		return is_file($file);
	}

	/**
	 * Returns TRUE if the provided path is a directory and FALSE if not.
	 *
	 * @param  string $directory Path to directory
	 * @return bool
	 */
	public static function isDirectory(string $directory): bool
	{
		return is_dir($directory);
	}

	/**
	 * Returns TRUE if a directory is empty and FALSE if not.
	 *
	 * @param  string $path Path to directory
	 * @return bool
	 */
	public static function isDirectoryEmpty(string $path): bool
	{
		$files = scandir($path);

		foreach($files as $file)
		{
			if($file !== '.' && $file !== '..')
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns TRUE if the file is readable and FALSE if not.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public static function isReadable(string $file): bool
	{
		return is_readable($file);
	}

	/**
	 * Returns TRUE if the file or directory is writable and FALSE if not.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public static function isWritable(string $file): bool
	{
		return is_writable($file);
	}

	/**
	 * Returns the time (unix timestamp) the file was last modified.
	 *
	 * @param  string $file Path to file
	 * @return int
	 */
	public static function modified(string $file): int
	{
		return filemtime($file);
	}

	/**
	 * Returns the fize of the file in bytes.
	 *
	 * @param  string $file Path to file
	 * @return int
	 */
	public static function size(string $file): int
	{
		return filesize($file);
	}

	/**
	 * Returns the extention of the file.
	 *
	 * @param  string $file Path to file
	 * @return string
	 */
	public static function extension(string $file): string
	{
		return pathinfo($file, PATHINFO_EXTENSION);
	}

	/**
	 * Returns the mime type of the file.
	 *
	 * @param  string      $file Path to file
	 * @return bool|string
	 */
	public static function mime(string $file)
	{
		// Just guess mime by using the file extension
		return Mime::fromExt(pathinfo($file, PATHINFO_EXTENSION));
	}

	/**
	 * Deletes the file from disk.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public static function delete(string $file): bool
	{
		if (self::exists($file) && self::isFile($file))
		{
			return unlink($file);
		}

		return false;
	}

	/**
	 * Renames a file.
	 *
	 * @param  string $src Path to old file
	 * @param  string $dst Path to new file
	 * @return bool
	 */
	public static function rename(string $src, string $dst): bool
	{
		return rename($src, $dst);
	}

	/**
	 * Creates a new empty file.
	 *
	 * @param  string $path Path to old file
	 * @return bool
	 */
	public static function touch(string $path): bool
	{
		return touch($path);
	}

	/**
	 * Deletes a directory and its contents from disk.
	 *
	 * @param  string $path Path to directory
	 * @return bool
	 */
	public static function deleteDirectory(string $path): bool
	{
		$iterator = new FilesystemIterator($path);

		foreach($iterator as $item)
		{
			if($item->isDir())
			{
				self::deleteDirectory($item->getPathname());
			}
			else
			{
				self::delete($item->getPathname());
			}
		}

		return rmdir($path);
	}

	/**
	 * Deletes a directory contents from disk.
	 *
	 * @param string $path Path to directory
	 */
	public static function emptyDirectory(string $path): void
	{
		$iterator = new FilesystemIterator($path);

		foreach($iterator as $item)
		{
			if($item->isDir())
			{
				self::deleteDirectory($item->getPathname());
			}
			else
			{
				self::delete($item->getPathname());
			}
		}
	}

	/**
	 * Returns an array of pathnames matching the provided pattern.
	 *
	 * @param  string      $pattern Patern
	 * @param  int         $flags   Flags
	 * @return array|false
	 */
	public static function glob(string $pattern, int $flags = 0)
	{
		return glob($pattern, $flags);
	}

	/**
	 * Returns an array of pathnames from a directory.
	 *
	 * @param  string $dir      Directory to list
	 * @param  array  $excludes File names to exclude
	 * @return array
	 */
	public static function list(string $dir, array $excludes = ['..', '.', '.ds_store', '.DS_Store']): array
	{
		return array_values(array_diff(scandir($dir), $excludes));
	}

	/**
	 * Returns the contents of the file.
	 *
	 * @param  string      $file File path
	 * @return bool|string
	 */
	public static function getContents(string $file)
	{
		return file_get_contents($file);
	}

	/**
	 * Writes the supplied data to a file.
	 *
	 * @param  string   $file File path
	 * @param  string   $data File data
	 * @param  bool     $lock Acquire an exclusive write lock? (optional) (default FALSE)
	 * @return bool|int
	 */
	public static function putContents(string $file, string $data, bool $lock = false)
	{
		return file_put_contents($file, $data, $lock ? LOCK_EX : 0);
	}

	/**
	 * Prepends the supplied data to a file.
	 *
	 * @param  string   $file File path
	 * @param  string   $data File data
	 * @param  bool     $lock Acquire an exclusive write lock? (optional) (default FALSE)
	 * @return bool|int
	 */
	public static function prependContents(string $file, string $data, bool $lock = false)
	{
		return file_put_contents($file, $data . file_get_contents($file), $lock ? LOCK_EX : 0);
	}

	/**
	 * Appends the supplied data to a file.
	 *
	 * @param  string   $file File path
	 * @param  string   $data File data
	 * @param  bool     $lock Acquire an exclusive write lock? (optional) (default FALSE)
	 * @return bool|int
	 */
	public static function appendContents(string $file, string $data, bool $lock = false)
	{
		return file_put_contents($file, $data, $lock ? FILE_APPEND | LOCK_EX : FILE_APPEND);
	}

	/**
	 * Truncates a file.
	 *
	 * @param  string   $file File path
	 * @param  bool     $lock Acquire an exclusive write lock? (optional) (default FALSE)
	 * @return bool|int
	 */
	public static function truncateContents(string $file, bool $lock = false)
	{
		return (0 === file_put_contents($file, null, $lock ? LOCK_EX : 0));
	}

	/**
	 *  Creates a directory.
	 *
	 *  @param   string   $path       Path to directory
	 *  @param   int      $mode       Mode (optional) (default 0777)
	 *  @param   bool  $recursive  Recursive (optional) (default FALSE)
	 *  @return  bool
	 */
	public static function createDirectory(string $path, int $mode = 0777, bool $recursive = false)
	{
		return mkdir($path, $mode, $recursive);
	}

	/**
	 * Includes a file.
	 *
	 * @param  string $file Path to file
	 * @return mixed
	 */
	public function include(string $file): mixed
	{
		return include $file;
	}

	/**
	 * Includes a file it hasn't already been included.
	 *
	 * @param  string $file Path to file
	 * @return mixed
	 */
	public function includeOnce(string $file): mixed
	{
		return include_once $file;
	}

	/**
	 * Requires a file.
	 *
	 * @param  string $file Path to file
	 * @return mixed
	 */
	public function require(string $file): mixed
	{
		return require $file;
	}

	/**
	 * Requires a file if it hasn't already been required.
	 *
	 * @param  string $file Path to file
	 * @return mixed
	 */
	public function requireOnce(string $file): mixed
	{
		return require_once $file;
	}

	/**
	 * Returns a SplFileObject.
	 *
	 * @param  string        $file           Path to file
	 * @param  string        $openMode       Open mode
	 * @param  bool          $useIncludePath Use include path? (optional) (default FALSE)
	 * @return SplFileObject
	 */
	public static function file(string $file, string $openMode = 'r', bool $useIncludePath = false)
	{
		return new SplFileObject($file, $openMode, $useIncludePath);
	}

	/**
	 * Creates a temporary file and returns the handle.
	 *
	 * @return resource
	 */
	public static function tmpfile()
	{
		return tmpfile();
	}

	/**
	 * Read and return the contents of a php file.
	 *
	 * @param  string      $file Path to file
	 * @param  array       $vars Array of variables to extract
	 * @return string|null
	 */
	public static function ob_read(string $file, array $vars = [])
	{
		if (self::exists($file) && self::isFile($file))
		{
			ob_start();
            extract($vars);
        	include $file;
        	return ob_get_clean();
		}

		return null;
	}
}
