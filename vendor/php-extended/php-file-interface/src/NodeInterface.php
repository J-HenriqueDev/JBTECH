<?php declare(strict_types=1);

/*
 * This file is part of the php-extended/php-file-interface library
 *
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace PhpExtended\File;

use DateTimeInterface;
use RuntimeException;
use Stringable;

/**
 * NodeInterface interface file.
 *
 * This interface represents all the common actions for every node in the
 * filesystem.
 *
 * @author Anastaszor
 */
interface NodeInterface extends Stringable
{
	
	/**
	 * Gets the file system holding this node.
	 *
	 * @return FileSystemInterface
	 */
	public function getFileSystem() : FileSystemInterface;
	
	/**
	 * Gets the parent folder for this node. If this node is the root folder
	 * of the filesystem, then it is its own parent.
	 *
	 * @return FolderInterface
	 */
	public function getParentFolder() : FolderInterface;
	
	/**
	 * Gets whether this node exists for real on the filesystem.
	 *
	 * @return boolean
	 */
	public function exists() : bool;
	
	/**
	 * Ensures that this node exists on the file system. If it does not, this
	 * will create all the needed tree structure to have it exist.
	 *
	 * @return boolean
	 * @throws RuntimeException if the reading from the filesystem fails
	 */
	public function ensureExists() : bool;
	
	/**
	 * Gets the path from the root of the FileSystem to this node.
	 *
	 * @return string
	 */
	public function getFilesystemPath() : string;
	
	/**
	 * Gets the real path of this node onto the (real) filesystem.
	 *
	 * @return string
	 */
	public function getRealPath() : string;
	
	/**
	 * Gets the name of the node.
	 * 
	 * @return string
	 */
	public function getName() : string;
	
	/**
	 * Gets the extension of the node.
	 * 
	 * @return string
	 */
	public function getExtension() : string;
	
	/**
	 * Gets the name of the node without the given suffix.
	 * 
	 * @param ?string $suffix
	 * @return string
	 */
	public function getBasename(?string $suffix) : string;
	
	/**
	 * Gets the permissions of the files.
	 * 
	 * @return string
	 * @throws RuntimeException if the reading from the filesystem fails
	 */
	public function getPermissions() : string;
	
	/**
	 * Gets the inode for the file.
	 * 
	 * @return integer
	 * @throws RuntimeException if the reading from the filesystem fails
	 */
	public function getInodeNumber() : int;
	
	/**
	 * Gets the size of the node.
	 * 
	 * @return integer
	 * @throws RuntimeException if the reading from the filesystem fails
	 */
	public function getSize() : int;
	
	/**
	 * Gets the uid of the owner.
	 * 
	 * @return integer
	 * @throws RuntimeException if the reading from the filesystem fails
	 */
	public function getOwner() : int;
	
	/**
	 * Gets the gid of the owner.
	 * 
	 * @return integer
	 * @throws RuntimeException if the reading from the filesystem fails
	 */
	public function getGroup() : int;
	
	/**
	 * Gets the last access time of the node.
	 * 
	 * @return DateTimeInterface
	 * @throws RuntimeException if the reading from the filesystem fails
	 */
	public function getATime() : DateTimeInterface;
	
	/**
	 * Gets the last modified time of the node.
	 * 
	 * @return DateTimeInterface
	 * @throws RuntimeException if the reading from the filesystem fails
	 */
	public function getMTime() : DateTimeInterface;
	
	/**
	 * Gets the last change time of the node.
	 * 
	 * @return DateTimeInterface
	 * @throws RuntimeException if the reading from the filesystem fails
	 */
	public function getCTime() : DateTimeInterface;
	
	/**
	 * Gets whether this node is writeable.
	 * 
	 * @return boolean
	 */
	public function isWritable() : bool;
	
	/**
	 * Gets whether this node is readable.
	 * 
	 * @return boolean
	 */
	public function isReadable() : bool;
	
	/**
	 * Gets whether this node is executable.
	 * 
	 * @return boolean
	 */
	public function isExecutable() : bool;
	
}
