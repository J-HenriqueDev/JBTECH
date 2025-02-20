<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-simple-cache-filesystem library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

use PhpExtended\SimpleCache\SimpleCacheException;
use PHPUnit\Framework\TestCase;

/**
 * SimpleCacheExceptionTest test file.
 * 
 * @author Anastaszor
 * @covers \PhpExtended\SimpleCache\SimpleCacheException
 *
 * @internal
 *
 * @small
 */
class SimpleCacheExceptionTest extends TestCase
{
	
	/**
	 * The object to test.
	 * 
	 * @var SimpleCacheException
	 */
	protected SimpleCacheException $_object;
	
	public function testToString() : void
	{
		$this->assertStringContainsString(SimpleCacheException::class, $this->_object->__toString());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PHPUnit\Framework\TestCase::setUp()
	 */
	protected function setUp() : void
	{
		$this->_object = new SimpleCacheException();
	}
	
}
