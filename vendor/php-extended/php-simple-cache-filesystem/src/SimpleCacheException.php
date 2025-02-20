<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-simple-cache-filesystem library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace PhpExtended\SimpleCache;

use Psr\SimpleCache\CacheException;
use RuntimeException;

/**
 * SimpleCacheException class file.
 * 
 * This class represents a generic exception for the simple cache library.
 * 
 * @author Anastaszor
 */
class SimpleCacheException extends RuntimeException implements CacheException
{
	
	// nothing to add
	
}
