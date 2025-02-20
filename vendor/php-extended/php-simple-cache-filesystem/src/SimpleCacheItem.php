<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-simple-cache-filesystem library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace PhpExtended\SimpleCache;

use DateTimeInterface;
use Stringable;

/**
 * SimpleCacheItem class file.
 * 
 * This class is a simple container for any value when the cache item is
 * registered.
 * 
 * @author Anastaszor
 */
class SimpleCacheItem implements Stringable
{
	
	/**
	 * The value of the key.
	 * 
	 * @var ?string
	 */
	public ?string $key = null;
	
	/**
	 * The value of the item.
	 * 
	 * @var null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>>
	 */
	public $value = null;
	
	/**
	 * The time when this expires.
	 * 
	 * @var ?DateTimeInterface
	 */
	public ?DateTimeInterface $expires = null;
	
	/**
	 * {@inheritDoc}
	 * @see \Stringable::__toString()
	 */
	public function __toString() : string
	{
		return static::class.'@'.\spl_object_hash($this);
	}
	
}
