<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-file-interface library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace PhpExtended\File;

use Stringable;

/**
 * FilesystemVisitorInterface interface file.
 * 
 * A Visitor is an object that does treatments recursively with each file and
 * folder it encounters into the filesystem tree hierarchy.
 * 
 * @author Anastaszor
 */
interface FilesystemVisitorInterface extends Stringable
{
	
	/**
	 * Visits the given filesystem.
	 * 
	 * @param FileSystemInterface $fileSystem
	 * @return null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>>
	 */
	public function visitFilesystem(FileSystemInterface $fileSystem);
	
	/**
	 * Visits the given folder.
	 * 
	 * @param FolderInterface $folder
	 * @return null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>>
	 */
	public function visitFolder(FolderInterface $folder);
	
	/**
	 * Visits the given file.
	 * 
	 * @param FileInterface $file
	 * @return null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object|array<integer|string, null|boolean|integer|float|string|object>>
	 */
	public function visitFile(FileInterface $file);
	
}
