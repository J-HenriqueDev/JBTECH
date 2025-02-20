<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-simple-cache-filesystem library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

use PhpExtended\SimpleCache\SimpleCacheInvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * SimpleCacheInvalidArgumentExceptionTest test file.
 * 
 * @author Anastaszor
 * @covers \PhpExtended\SimpleCache\SimpleCacheInvalidArgumentException
 *
 * @internal
 *
 * @small
 */
class SimpleCacheInvalidArgumentExceptionTest extends TestCase
{
	
	/**
	 * The object to test.
	 * 
	 * @var SimpleCacheInvalidArgumentException
	 */
	protected SimpleCacheInvalidArgumentException $_object;
	
	public function testToString() : void
	{
		$this->assertStringContainsString(SimpleCacheInvalidArgumentException::class, $this->_object->__toString());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PHPUnit\Framework\TestCase::setUp()
	 */
	protected function setUp() : void
	{
		$this->_object = new SimpleCacheInvalidArgumentException();
	}
	
}
