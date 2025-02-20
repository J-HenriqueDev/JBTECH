<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-file-interface library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace PhpExtended\File;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * FileInterface interface file.
 *
 * This interface represents all the actions that can be made on a file node.
 *
 * @author Anastaszor
 */
interface FileInterface extends NodeInterface
{
	
	/**
	 * Gets the binary full contents of this file.
	 *
	 * @return string
	 * @throws RuntimeException if the size of the file is more than the
	 *                          available memory to get the full contents
	 */
	public function getFullContents() : string;
	
	/**
	 * Gets a stream from this file to be able to stream the data from the file.
	 * 
	 * @return StreamInterface
	 * @throws RuntimeException if the file is locked or the stream cannot be
	 *                          created
	 */
	public function getDataStream() : StreamInterface;
	
	/**
	 * Empties the file and adds the contents.
	 *
	 * @param string $rawContents
	 * @return integer the number of bytes written
	 * @throws RuntimeException if the writing fails
	 */
	public function overwrite(string $rawContents) : int;
	
	/**
	 * Empties the file and adds the contents from another given file.
	 * 
	 * @param StreamInterface $stream
	 * @return integer the number of bytes written
	 * @throws RuntimeException if the writing fails
	 */
	public function overwriteStream(StreamInterface $stream) : int;
	
	/**
	 * Appends content at the end of the file.
	 *
	 * @param string $rawContents
	 * @return integer the number of bytes written
	 * @throws RuntimeException if the writing fails
	 */
	public function append(string $rawContents) : int;
	
	/**
	 * Appends the contents of the stream at the end of the file.
	 * 
	 * @param StreamInterface $stream
	 * @return integer the number of bytes written
	 * @throws RuntimeException if the writing fails
	 */
	public function appendStream(StreamInterface $stream) : int;
	
	/**
	 * Gets this file visited by the given visitor.
	 * 
	 * @param FilesystemVisitorInterface $visitor
	 * @return null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>>
	 */
	public function beVisitedBy(FilesystemVisitorInterface $visitor);
	
}
