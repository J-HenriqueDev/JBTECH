<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-file-interface library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace PhpExtended\File;

use InvalidArgumentException;
use Stringable;

/**
 * FileSystemInterface interface file.
 *
 * A FileSystem is a class which represents a file hierarchy. It can be placed
 * anywhere on a real filesystem, and which guarantees that all files that
 * depends from this FileSystem will be inside the tree of the root folder this
 * filesystem is placed on.
 *
 * @author Anastaszor
 */
interface FileSystemInterface extends Stringable
{
	
	/**
	 * Gets the absolute path of the root node of this FileSystem.
	 *
	 * @return string
	 */
	public function getAbsolutePath() : string;
	
	/**
	 * Creates a new folder from the given filesystem where the relative path
	 * points to somewhere inside the root folder of the FileSystem.
	 *
	 * @param string $relativePath
	 * @return FolderInterface
	 * @throws InvalidArgumentException if the file does not point to a valid folder path
	 */
	public function getFolder(string $relativePath) : FolderInterface;
	
	/**
	 * Creates a new file from the given filesystem where the relative path
	 * points to somewhere inside the root folder of the FileSystem.
	 *
	 * @param string $relativePath
	 * @return FileInterface
	 * @throws InvalidArgumentException if the file does not point to a valid file path
	 */
	public function getFile(string $relativePath) : FileInterface;
	
	/**
	 * Gets this filesystem visited by the given visitor.
	 * 
	 * @param FilesystemVisitorInterface $visitor
	 * @return null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>>
	 */
	public function beVisitedBy(FilesystemVisitorInterface $visitor);
	
}
