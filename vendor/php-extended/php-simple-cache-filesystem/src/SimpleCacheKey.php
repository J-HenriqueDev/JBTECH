<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-simple-cache-filesystem library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace PhpExtended\SimpleCache;

use Stringable;

/**
 * SimpleCacheFilesystem class file.
 *
 * This class makes a cache of any folder on a filesystem.
 *
 * @author Anastaszor
 */
class SimpleCacheKey implements Stringable
{
	
	/**
	 * The hashed value of the key.
	 * 
	 * @var string
	 */
	protected string $_hash;
	
	/**
	 * Gets the hash corresponding for the given key.
	 *
	 * @param null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>> $key
	 */
	public function __construct($key)
	{
		if(\is_object($key) || \is_array($key))
		{
			$key = \serialize($key);
		}
		
		$this->_hash = (string) \hash('sha512', (string) $key);
	}
	
	/**
	 * {@inheritDoc}
	 * @see Stringable::__toString()
	 */
	public function __toString() : string
	{
		return $this->_hash;
	}
	
	/**
	 * Gets the first part of the hash.
	 * 
	 * @return string
	 */
	public function getFirstLevel() : string
	{
		return (string) \mb_substr($this->_hash, 0, 2, '8bit');
	}
	
	/**
	 * Gets the second level of the key.
	 * 
	 * @return string
	 */
	public function getSecondLevel() : string
	{
		return (string) \mb_substr($this->_hash, 2, 2, '8bit');
	}
	
	/**
	 * Gets the last level of the key.
	 * 
	 * @return string
	 */
	public function getThirdLevel() : string
	{
		return (string) \mb_substr($this->_hash, 4, null, '8bit');
	}
	
	/**
	 * Gets the full path to the given file represented by this key.
	 * 
	 * @return string
	 */
	public function getPath() : string
	{
		return '/'.$this->getFirstLevel().'/'.$this->getSecondLevel().'/'.$this->getThirdLevel();
	}
	
}
