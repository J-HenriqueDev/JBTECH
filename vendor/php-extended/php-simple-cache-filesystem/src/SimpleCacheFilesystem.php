<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-simple-cache-filesystem library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace PhpExtended\SimpleCache;

use DateInterval;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use PhpExtended\File\FileSystemInterface;
use Psr\SimpleCache\CacheInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Stringable;

/**
 * SimpleCacheFilesystem class file.
 * 
 * This class makes a cache of any folder on a filesystem.
 * 
 * @author Anastaszor
 * @todo use php-extended/php-file FileSystem
 */
class SimpleCacheFilesystem implements CacheInterface, Stringable
{
	
	/**
	 * The underlying filesystem.
	 * 
	 * @var FileSystemInterface
	 */
	protected FileSystemInterface $_fileSystem;
	
	/**
	 * Builds a new SimpleCacheFilesystem based on the given folder.
	 * 
	 * @param FileSystemInterface $fileSystem
	 */
	public function __construct(FileSystemInterface $fileSystem)
	{
		$this->_fileSystem = $fileSystem;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Stringable::__toString()
	 */
	public function __toString() : string
	{
		return static::class.'@'.\spl_object_hash($this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Psr\SimpleCache\CacheInterface::get()
	 * @param string $key
	 * @param null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>> $default
	 * @return null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>>
	 * @psalm-suppress MoreSpecificImplementedParamType
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function get(string $key, mixed $default = null) : mixed
	{
		$hash = new SimpleCacheKey($key);
		
		try
		{
			$file = $this->_fileSystem->getFile($hash->getPath());
		}
		// @codeCoverageIgnorestart
		catch(InvalidArgumentException $exc)
		{
			// should not happen with getPath
			return $default;
		}
		// @codeCoverageIgnoreEnd
		
		if(!$file->exists())
		{
			return $default;
		}
		
		try
		{
			$data = $file->getFullContents();
		}
		catch(RuntimeException $exc)
		{
			return $default;
		}
		
		if(empty($data))
		{
			return $default;
		}
		
		$unserialized = \unserialize($data);
		if(false === $unserialized)
		{
			return $default;
		}
		
		if(!$unserialized instanceof SimpleCacheItem)
		{
			return $default;
		}
		
		$now = new DateTimeImmutable();
		if(!$unserialized->expires instanceof DateTimeImmutable)
		{
			return $default;
		}
		
		$diff = $now->getTimestamp() - $unserialized->expires->getTimestamp();
		if(0 < $diff)	// now is after expires : cache miss
		{
			return $default;
		}
		
		// everything ok : cache hit
		return $unserialized->value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Psr\SimpleCache\CacheInterface::set()
	 */
	public function set(string $key, mixed $value, null|int|DateInterval $ttl = null) : bool
	{
		$hash = new SimpleCacheKey($key);
		
		$item = new SimpleCacheItem();
		$item->key = $key;
		$item->value = $value;
		if(null === $ttl)
		{
			$ttl = 36000;	// 10 hours
		}
		
		if(\is_int($ttl))
		{
			/** @var DateInterval $ttl */
			$ttl = DateInterval::createFromDateString('+'.((string) $ttl).' seconds');
		}
		
		$now = new DateTimeImmutable();
		$future = $now->add($ttl);
		$item->expires = $future;
		$serialized = \serialize($item);
		
		try
		{
			$file = $this->_fileSystem->getFile($hash->getPath());
		}
		// @codeCoverageIgnoreStart
		catch(InvalidArgumentException $exc)
		{
			// should not happen with getPath
			return false;
		}
		// @codeCoverageIgnoreEnd
		
		try
		{
			$bytes = $file->overwrite($serialized);
		}
		catch(RuntimeException $exc)
		{
			return false;
		}
		
		return 0 < $bytes;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Psr\SimpleCache\CacheInterface::delete()
	 * @param string $key
	 */
	public function delete(string $key) : bool
	{
		$hash = new SimpleCacheKey($key);
		
		try
		{
			$path = $this->_fileSystem->getFile($hash->getPath())->getFilesystemPath();
		}
		// @codeCoverageIgnoreStart
		catch(InvalidArgumentException $exc)
		{
			// should not happen with getPath
			return false;
		}
		// @codeCoverageIgnoreEnd
		
		if(!\is_file($path) || !\is_writable($path))
		{
			return true;
		}
		
		return \unlink($path);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Psr\SimpleCache\CacheInterface::clear()
	 */
	public function clear() : bool
	{
		$success = true;
		
		try
		{
			$recursiveIterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$this->_fileSystem->getAbsolutePath(),
					RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
					| RecursiveDirectoryIterator::KEY_AS_PATHNAME
					| RecursiveDirectoryIterator::SKIP_DOTS
					| RecursiveDirectoryIterator::UNIX_PATHS,
				),
				RecursiveIteratorIterator::CHILD_FIRST,
			);
			
			/** @var SplFileInfo $splFileInfo */
			foreach($recursiveIterator as $splFileInfo)
			{
				if($splFileInfo->getPathname() === $this->_fileSystem->getAbsolutePath())
				{
					continue;
				}
				
				if($splFileInfo->isDir())
				{
					$success = $success && \rmdir($splFileInfo->getPathname());
				}
				
				if(($splFileInfo->isFile() || $splFileInfo->isLink()))
				{
					$success = $success && \unlink($splFileInfo->getPathname());
				}
			}
		}
		catch(Exception $e)
		{
			return false;
		}
		
		return (bool) $success;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Psr\SimpleCache\CacheInterface::getMultiple()
	 * @param iterable<string> $keys
	 * @param null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>> $default
	 * @return array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>>>
	 * @psalm-suppress MoreSpecificImplementedParamType,ImplementedReturnTypeMismatch
	 */
	public function getMultiple(iterable $keys, mixed $default = null) : iterable
	{
		$values = [];
		
		foreach($keys as $key)
		{
			$values[$key] = $this->get((string) $key, $default);
		}
		
		return $values;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Psr\SimpleCache\CacheInterface::setMultiple()
	 * @param iterable<string> $values
	 * @param null|int|DateInterval $ttl
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	public function setMultiple(iterable $values, null|int|DateInterval $ttl = null) : bool
	{
		$success = true;
		
		foreach($values as $key => $value)
		{
			$success = $success && $this->set((string) $key, $value, $ttl);
		}
		
		return (bool) $success;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Psr\SimpleCache\CacheInterface::deleteMultiple()
	 * @param iterable<string> $keys
	 */
	public function deleteMultiple(iterable $keys) : bool
	{
		$success = true;
		
		foreach($keys as $key)
		{
			$success = $success && (bool) ($this->delete((string) $key));
		}
		
		return (bool) $success;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Psr\SimpleCache\CacheInterface::has()
	 */
	public function has(string $key) : bool
	{
		$hash = new SimpleCacheKey($key);
		
		try
		{
			return $this->_fileSystem->getFile($hash->getPath())->exists();
		}
		// @codeCoverageIgnoreStart
		catch(InvalidArgumentException $exc)
		{
			// should not happen with getPath
			return false;
		}
		// @codeCoverageIgnoreEnd
	}
	
}
