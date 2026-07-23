<?php

namespace RPC\View;

use \Exception;

/**
 * Implements a basic caching mechanism for views
 * 
 * @package View
 */
class Cache
{
	
	/**
	 * Directory path where cached templates are stored
	 * 
	 * @var string
	 */
	protected $directory = '';
	
	/**
	 * Instantiates an object with a given directory to store templates
	 * 
	 * @param string $path
	 */
	public function __construct( $path )
	{
		$this->setDirectory( $path );
	}
	
	/**
	 * Sets the path where templates will be cached
	 *
	 * @param string $path
	 *
	 * @return \RPC\View\Cache
	 */
	public function setDirectory( $path )
	{
		if( ! is_dir( $path ) )
		{
			// SECURITY: Use more restrictive permissions (0750 instead of 0777)
			// Owner: rwx, Group: r-x, Other: none
			mkdir( $path, 0750, true );
		}

		if( ! is_writable( $path ) )
		{
			// SECURITY: Use 0750 instead of 0777
			chmod( $path, 0750 );
		}

		$this->directory = realpath( $path );

		return $this;
	}
	
	/**
	 * Returns the directory where cached templates are stored
	 * 
	 * @return string
	 */
	public function getDirectory()
	{
		return $this->directory;
	}
	
	/**
	 * Returns a path to the cached version of the given template
	 *
	 * @param string $file
	 *
	 * @return string|false
	 */
	public function get( $file, $template_name )
	{
		$template_name = preg_replace( '/[^a-zA-Z0-9_]/', '_', str_replace( '.php', '', $template_name ) );
		$path = $this->getPathForFile( $file, $template_name );

		// Check if cached file exists
		if( ! file_exists( $path ) )
		{
			return false;
		}

		// Optimize: Single stat call instead of multiple filemtime() calls
		$file_stat = @stat( $file );
		$cache_stat = @stat( $path );

		if( $file_stat === false || $cache_stat === false )
		{
			@unlink( $path );
			return false;
		}

		// If source file is newer than cache, invalidate cache
		if( $file_stat['mtime'] > $cache_stat['mtime'] )
		{
			// Invalidate opcode cache if enabled
			if( function_exists( 'opcache_invalidate' ) )
			{
				@opcache_invalidate( $path, true );
			}

			@unlink( $path );
			return false;
		}

		return $path;
	}
	
	/**
	 * Generates the path where a certain file will be written
	 *
	 * @param string $file
	 * @param string $nice_name
	 *
	 * @return string
	 */
	protected function getPathForFile( $file, $nice_name )
	{
		// Use faster hash for cache key generation (xxh3 if available, otherwise crc32)
		if( function_exists( 'hash' ) && in_array( 'xxh3', hash_algos() ) )
		{
			$hash = hash( 'xxh3', $file );
		}
		else
		{
			// crc32 is much faster than md5 and sufficient for cache keys
			$hash = sprintf( '%08x', crc32( $file ) );
		}

		return $this->getDirectory() . DIRECTORY_SEPARATOR . $nice_name .  '_' . $hash . '.php';
	}
	
	/**
	 * Caches the content of a template
	 *
	 * @param string $file
	 * @param string $content
	 * @param string $template_name
	 *
	 * @return \RPC\View\Cache
	 */
	public function set( $file, $content, $template_name )
	{
		$template_name = preg_replace( '/[^a-zA-Z0-9_]/', '_', str_replace( '.php', '', $template_name ) );
		$cache_path = $this->getPathForFile( $file, $template_name );

		// Write atomically using temp file + rename to prevent partial writes
		$temp_path = $cache_path . '.' . uniqid( 'tmp', true );

		if( file_put_contents( $temp_path, $content, LOCK_EX ) === false )
		{
			@unlink( $temp_path );
			throw new \Exception( 'Cannot write cached version of template "' . $file  . '" to "' . $cache_path . '"' );
		}

		// SECURITY: Set restrictive permissions on cache file (0640)
		// Owner: rw, Group: r, Other: none
		chmod( $temp_path, 0640 );

		// Atomic rename
		if( ! rename( $temp_path, $cache_path ) )
		{
			@unlink( $temp_path );
			throw new \Exception( 'Cannot rename temp cache file to "' . $cache_path . '"' );
		}

		return $this;
	}
	
}

?>
