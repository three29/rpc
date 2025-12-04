<?php

namespace RPC;

use RPC\Exception\RuntimeException;

/**
 * Session class which provides a few convenience methods. It is
 * designed to allow users to work with $_SESSION.
 *
 * @package Core
 */
class Session
{

	/**
	 * Timestamp when the session would expire
	 * 
	 * @var int
	 */
	protected $_rpc_expire = 0;
	
	/**
	 * The path for which the session would be available
	 * 
	 * @var string
	 */
	protected $_rpc_path = '/';
	
	/**
	 * The domain for which the session would be available
	 * 
	 * @var string
	 */
	protected $_rpc_domain = '';
	
	/**
	 * Whether the session should be sent only over a HTTPS connection
	 *
	 * @var bool
	 */
	protected $_rpc_secure = false;
	
	/**
	 * Whether the session will be available only over HTTP connections
	 * 
	 * @var bool
	 */
	protected $_rpc_httponly = false;
	
	/**
	 * Class instance
	 *
	 * @var \RPC\Session|null
	 */
	protected static ?\RPC\Session $_rpc_instance = null;
	
	/**
	 * Class constructor
	 * Now supports dependency injection while maintaining getInstance() for backward compatibility
	 */
	public function __construct()
	{
		$this->setDefaultCookieParams();
	}

	/**
	 * Get singleton instance (backward compatibility)
	 *
	 * @return \RPC\Session
	 */
	public static function getInstance()
	{
		if( ! isset( self::$_rpc_instance ) )
		{
			self::$_rpc_instance = new self();
		}

		return self::$_rpc_instance;
	}

	/**
	 * Prevent session from being cloned
	 *
	 * @throws \Exception
	 */
	public function __clone()
	{
		throw new RuntimeException("Singletons can't be cloned");
	}
	
	/**
	 * Sets a name for the current's application session cookie
	 * Each application should have a different session name
	 * 
	 * @param string $name
	 * 
	 * @return \RPC\Session
	 */
	public function setName( $name )
	{
		session_name( $name );
		
		return $this;
	}
	
	/**
	 * Returns the session's cookie name
	 * 
	 * @return string
	 */
	public function getName()
	{
		return session_name();
	}
	
	/**
	 * Specifies the folder where sessions will be stored, when a file system
	 * adapter is used
	 * 
	 * @return \RPC\Session
	 */
	public function setSavePath( $path )
	{
		session_save_path( $path );
		
		return $this;
	}
	
	/**
	 * Sets a timestamp when the session will expire
	 * 
	 * @param int $expire
	 * 
	 * @return \RPC\Session
	 */
	public function setExpire( $expire )
	{
		$this->_rpc_expire = $expire;
		
		return $this;
	}
	
	/**
	 * Sets the path for which the session will be available
	 * 
	 * @param string $path
	 * 
	 * @return \RPC\Session
	 */
	public function setPath( $path )
	{
		$this->_rpc_path = $path;
		
		return $this;
	}
	
	/**
	 * Sets the domain for which the session will be available
	 * 
	 * @param string $domain
	 * 
	 * @return \RPC\Session
	 */
	public function setDomain( $domain )
	{
		$this->_rpc_domain = $domain;
		
		return $this;
	}
	
	/**
	 * Sets whether the session will be sent only over a HTTPS connection
	 * 
	 * @param bool $secure
	 * 
	 * @return \RPC\Session
	 */
	public function setSecure( $secure )
	{
		$this->_rpc_secure = $secure;
		
		return $this;
	}
	
	/**
	 * Sets whether the session will be sent only over HTTP connections
	 * 
	 * @param bool $httponly
	 * 
	 * @return \RPC\Session
	 */
	public function setHTTPOnly( $httponly )
	{
		$this->_rpc_httponly = $httponly;
		
		return $this;
	}
	
	/**
	 * Set current cache expire
	 * 
	 * @param int $expire Expire time in seconds
	 * 
	 * @return \RPC\Session
	 */
	public function setCacheExpire( $expire )
	{
		session_cache_expire( (int) round( $expire / 60 ) );

		return $this;
	}
	
	/**
	 * The cache limiter defines which cache control HTTP headers are sent
	 * to the client. These headers determine the rules by which the page
	 * content may be cached by the client and intermediate proxies.
	 * Setting the cache limiter to nocache disallows any client/proxy
	 * caching. A value of public permits caching by proxies and the client,
	 * whereas private disallows caching by proxies and permits the client
	 * to cache the contents.
	 * 
	 * In private mode, the Expire header sent to the client may cause
	 * confusion for some browsers, including Mozilla. You can avoid this
	 * problem by using private_no_expire mode. The expire header is never
	 * sent to the client in this mode.
	 * 
	 * @param string $limiter
	 * 
	 * @return \RPC\Session
	 */
	public function setCacheLimiter( $limiter )
	{
		session_cache_limiter( $limiter );
		
		return $this;
	}
	
	/**
	 * Gives a path to an external resource (file) which will be used as an
	 * additional entropy source in the session id creation process
	 * 
	 * @return \RPC\Session
	 */
	public function setEntropyFile( $path )
	{
		// session.entropy_file was removed in PHP 7.1
		// PHP now uses a secure random number generator by default
		// This method is kept for backwards compatibility but does nothing

		return $this;
	}
	
	/**
	 * Specifies the number of bytes which will be read from the file specified by
	 * the entropy file
	 *
	 * @deprecated Removed in PHP 7.1 - session.entropy_length no longer exists
	 * @return \RPC\Session
	 */
	public function setEntropyLength( $length )
	{
		// session.entropy_length was removed in PHP 7.1
		// PHP now uses a secure random number generator by default
		// This method is kept for backwards compatibility but does nothing

		return $this;
	}
	
	/**
	 * Allows you to specify the hash algorithm used to generate the session IDs. '0' means MD5 (128 bits) and '1' means SHA-1 (160 bits)
	 *
	 * @deprecated Removed in PHP 7.1 - session.hash_function no longer exists
	 * @return \RPC\Session
	 */
	public function setHashFunction( $function )
	{
		// session.hash_function was removed in PHP 7.1
		// Use session.sid_length and session.sid_bits_per_character instead
		// This method is kept for backwards compatibility but does nothing

		return $this;
	}
	
	/**
	 * Session will not be available if cookies are not allowed
	 * 
	 * @return \RPC\Session
	 */
	public function useOnlyCookies( $value )
	{
		ini_set( 'session.use_only_cookies', $value );
		
		return $this;
	}
	
	/**
	 * Sets a save adapter for the session. The object will provide a
	 * medium to keep the session data.
	 *
	 * @param \RPC\Session\Adapter $adapter
	 *
	 * @return \RPC\Session
	 * @phpstan-ignore-next-line Session\Adapter class not yet implemented
	 */
	public function setAdapter( \RPC\Session\Adapter $adapter )
	{
		session_set_save_handler( array( $adapter, 'open' ),
		                          array( $adapter, 'close' ),
		                          array( $adapter, 'read' ),
		                          array( $adapter, 'write' ),
		                          array( $adapter, 'destroy' ),
		                          array( $adapter, 'gc' ) );
		
		return $this;
	}
	
	/**
	 * Generates a new session id and removes the old session file
	 * 
	 * @retun \RPC\Session
	 */
	public function regenerateId()
	{
		session_regenerate_id( true );
		
		return $this;
	}
	
	/**
	 * Initializes the session
	 *
	 * @return \RPC\Session
	 */
	public function start()
	{
		// PHP 7.3+ supports array format with samesite option
		if( PHP_VERSION_ID >= 70300 )
		{
			session_set_cookie_params([
				'lifetime' => $this->_rpc_expire,
				'path' => $this->_rpc_path,
				'domain' => $this->_rpc_domain,
				'secure' => $this->_rpc_secure,
				'httponly' => $this->_rpc_httponly,
				'samesite' => 'Lax'
			]);
		}
		else
		{
			session_set_cookie_params( $this->_rpc_expire, $this->_rpc_path, $this->_rpc_domain, $this->_rpc_secure, $this->_rpc_httponly );
		}

		session_start();

		//fixation attacks
		if( ! isset( $_SESSION['initiated'] ) )
		{
		    session_regenerate_id( true );
		    $_SESSION['initiated'] = true;
		}

		//session hijacking
		if( isset( $_SESSION['HTTP_USER_AGENT'] ) )
		{
		    if( ! hash_equals( $_SESSION['HTTP_USER_AGENT'], hash_hmac( 'sha256', @$_SERVER['HTTP_USER_AGENT'], session_id() ) ) )
		    {
		        /* Prompt for password */
		        $this->destroy();
		        return $this->start();
		    }
		}
		else
		{
		    $_SESSION['HTTP_USER_AGENT'] = hash_hmac( 'sha256', @$_SERVER['HTTP_USER_AGENT'], session_id() );
		}
		
		return $this;
	}
	
	/**
	 * Writes and closes the session
	 */
	public function write()
	{
		session_write_close();
	}
	
	/**
	 * Removes the session and regenerates a new session id
	 */
	public function destroy()
	{
		unset( $_SESSION );
		session_destroy();
	}
	
	/**
	 * When the session is initialized, the default parameters are set
	 */
	public function setDefaultCookieParams()
	{
		$params = session_get_cookie_params();
		
		$this->_rpc_expire   = $params['lifetime'];
		$this->_rpc_path     = $params['path'];
		$this->_rpc_domain   = $params['domain'];
		$this->_rpc_secure   = $params['secure'];
		$this->_rpc_httponly = $params['httponly'];
	}
	
}

?>
