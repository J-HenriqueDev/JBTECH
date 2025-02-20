<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-simple-cache-filesystem library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

use PhpExtended\SimpleCache\SimpleCacheKey;
use PHPUnit\Framework\TestCase;

/**
 * SimpleCacheKeyTest test file.
 * 
 * @author Anastaszor
 * @covers \PhpExtended\SimpleCache\SimpleCacheKey
 *
 * @internal
 *
 * @small
 */
class SimpleCacheKeyTest extends TestCase
{
	
	/**
	 * The object to test.
	 * 
	 * @var SimpleCacheKey
	 */
	protected SimpleCacheKey $_object;
	
	public function testToString() : void
	{
		$this->assertEquals('860adf34f8a466493027dfe37f2eeb7458caaf49f85bdcc8ec77487a0054c92bdcf705036fe27f734b018afe824ad1f0dad6ea58b869c910d3d58f8f22286ac7', $this->_object->__toString());
	}
	
	public function testGetPath() : void
	{
		$this->assertEquals('/86/0a/df34f8a466493027dfe37f2eeb7458caaf49f85bdcc8ec77487a0054c92bdcf705036fe27f734b018afe824ad1f0dad6ea58b869c910d3d58f8f22286ac7', $this->_object->getPath());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PHPUnit\Framework\TestCase::setUp()
	 */
	protected function setUp() : void
	{
		$this->_object = new SimpleCacheKey(['key']);
	}
	
}
