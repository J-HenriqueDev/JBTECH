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
use Iterator;

/**
 * FolderInterface interface file.
 *
 * This interface represents all the actions that can be made on a folder node.
 *
 * @author Anastaszor
 */
interface FolderInterface extends NodeInterface
{
	
	/**
	 * Clears the local cache for the list of folders and files, if the
	 * implementation has one.
	 * 
	 * @return boolean
	 */
	public function clearCache() : bool;
	
	/**
	 * Gets all the folders that exists in this folder.
	 *
	 * @return Iterator<FolderInterface>
	 */
	public function listFolders() : Iterator;
	
	/**
	 * Gets all the files that exists in this folder.
	 *
	 * @return Iterator<FileInterface>
	 */
	public function listFiles() : Iterator;
	
	/**
	 * Creates a new folder from the given filesystem where the relative path
	 * points to somewhere inside this folder.
	 *
	 * @param string $name
	 * @return FolderInterface
	 * @throws InvalidArgumentException if the file does not point to a valid folder path
	 */
	public function getFolder(string $name) : FolderInterface;
	
	/**
	 * Creates a new file from the given filesystem where the relative path
	 * points to somewhere inside this folder.
	 *
	 * @param string $relativePath
	 * @return FileInterface
	 * @throws InvalidArgumentException if the file does not point to a valid file path
	 */
	public function getFile(string $relativePath) : FileInterface;
	
	/**
	 * Gets this folder visited by the given visitor.
	 * 
	 * @param FilesystemVisitorInterface $visitor
	 * @return null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>>
	 */
	public function beVisitedBy(FilesystemVisitorInterface $visitor);
	
}
