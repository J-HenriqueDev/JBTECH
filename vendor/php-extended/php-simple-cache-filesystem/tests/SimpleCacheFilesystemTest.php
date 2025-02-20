<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-simple-cache-filesystem library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

use PhpExtended\File\FileSystemInterface;
use PhpExtended\SimpleCache\SimpleCacheFilesystem;
use PHPUnit\Framework\TestCase;

/**
 * SimpleCacheFilesystemTest test file.
 * 
 * @author Anastaszor
 * @covers \PhpExtended\SimpleCache\SimpleCacheFilesystem
 *
 * @internal
 *
 * @small
 */
class SimpleCacheFilesystemTest extends TestCase
{
	
	/**
	 * The object to test.
	 * 
	 * @var SimpleCacheFilesystem
	 */
	protected SimpleCacheFilesystem $_object;
	
	public function testToString() : void
	{
		$this->assertEquals(\get_class($this->_object).'@'.\spl_object_hash($this->_object), $this->_object->__toString());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PHPUnit\Framework\TestCase::setUp()
	 */
	protected function setUp() : void
	{
		$this->_object = new SimpleCacheFilesystem($this->createMock(FileSystemInterface::class));
	}
	
}
