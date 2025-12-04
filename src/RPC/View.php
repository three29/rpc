<?php

namespace RPC;

use RPC\Exception\ViewException;
use RPC\Exception\InvalidArgumentException;
use RPC\Exception\NotFoundException;
use RPC\HTTP\Response;
use RPC\View\Cache;
use RPC\View\Filter\Form;
use RPC\Signal;


/**
 * Used to render HTML content
 *
 * @package View
 */
class View
{
	protected $controller;
	/**
	 * Assigned variables throughout the script
	 *
	 * @var array
	 */
	protected $_view_vars = array();

	/**
	 * Cache object
	 *
	 * @var object
	 */
	protected $_view_cache = null;

	/**
	 * Base directory for templates
	 *
	 * @var string
	 */
	protected $_view_tpldir = '';

	protected $_view_filters = array();


	protected $_view_errors = array();

	/**
	 * Default filters
	 *
	 * @var array
	 */
	protected $_view_defaultfilters = array
	(
		'\RPC\View\Filter\Form',
		'\RPC\View\Filter\Echoo',
		'\RPC\View\Filter\Render',
		'\RPC\View\Filter\Placeholder',
		'\RPC\View\Filter\Datagrid',
		'\RPC\View\Filter\Error'
	);

	/**
	 * Registered filters
	 *
	 * @var array
	 */
	protected $_view_registeredfilters = array();

	/**
	 * Template that is currently being rendered
	 *
	 * @var string
	 */
	protected $current_template = '';

	/**
	 * HTTP Request object
	 *
	 * @var \RPC\HTTP\Request
	 */
	public $request;

	/**
	 * HTTP Response object
	 *
	 * @var \RPC\HTTP\Response
	 */
	public $response;

	/**
	 * Class constructor which adds the default filters and some needed
	 * variables
	 */
	public function __construct( string $dir, \RPC\View\Cache $cache )
	{
		if( ! is_dir( $dir ) )
		{
			throw new ViewException( 'The given path does not point to a directory' );
		}

		$this->_view_tpldir = realpath( $dir );
		$this->_view_cache  = $cache;


		$this->setRequest( \RPC\HTTP\Request::getInstance() );
		$this->setResponse( Response::getInstance() );

		foreach( $this->_view_defaultfilters as $v )
		{
			$this->registerFilter( $v );
		}
	}

	/**
	 * Returns the set template directory
	 *
	 * @return string
	 */
	public function getTemplateDirectory(): string
	{
		return $this->_view_tpldir;
	}

	/**
	 * Set the template directory
	 *
	 * @param string $dir template directory path
	 */
	public function setTemplateDirectory(string $dir): void
	{
		$this->_view_tpldir = realpath($dir);
	}

	/**
	 * Set the HTTP Response object
	 *
	 * @param Response $response
	 */
	public function setResponse( Response $response ): void
	{
		$this->response = $response;
	}

	/**
	 * Get the HTTP Response object
	 *
	 * @return Response
	 */
	public function getResponse(): Response
	{
		return $this->response;
	}

	/**
	 * Set the HTTP Request object
	 *
	 * @param \RPC\HTTP\Request $request
	 */
	public function setRequest( \RPC\HTTP\Request $request ): void
	{
		$this->request = $request;
	}

	/**
	 * Returns the HTTP Request object
	 *
	 * @return \RPC\HTTP\Request
	 */
	public function getRequest(): \RPC\HTTP\Request
	{
		return $this->request;
	}

	/**
	 * Escapes all HTML characters from the given string
	 *
	 * @param string $str
	 *
	 * @return string Escaped string
	 */
	public function escape( string $str ): string
	{
		return htmlentities( $str, ENT_QUOTES, 'UTF-8', false );
	}

	/**
	 * Returns the array of defined variables
	 *
	 * @return array
	 */
	public function getVars(): array
	{
		return $this->_view_vars;
	}

	/**
	 * Registers a filter with the view, and in case the filter has external
	 * functionality (for example, the \RPC\View\Error filter has to be accessed
	 * from outside, so that errors can be set and fetched) provides a name
	 * which will allow access to the object
	 *
	 * @param string $class_name
	 *
	 * @return self
	 */
	public function registerFilter( string $class_name ): self
	{
		$name = explode( '\\', $class_name );
		$name = strtolower( end( $name ) );

		$this->_view_registeredfilters[$name] = array( 'class_name' => $class_name, 'instance' => null );

		return $this;
	}

	/**
	 * Removes all filters registered in the constructor
	 */
	public function removeDefaultFilters(): void
	{
		foreach( $this->_view_defaultfilters as $v )
		{
			$name = explode( '\\', $v );
			$name = end( $name );

			unset( $this->_view_registeredfilters[$name] );
		}
	}

	/**
	 * Unregisters a filter from the queue
	 *
	 * @param string $filter
	 *
	 * @return self
	 */
	public function unregisterFilter( string $filter ): self
	{
		$name = explode( '\\', $filter );
		$name = end( $name );

		unset( $this->_view_registeredfilters[$name] );

		return $this;
	}

	/**
	 * Returns the parser's cache object
	 *
	 * @return\RPC\View\Cache
	 */
	public function getCache(): Cache
	{
		return $this->_view_cache;
	}

	/**
	 * Set the view cache
	 *
	 * @param \RPC\View\Cache $cache the parser's cache object
	 */
	public function setCache( Cache $cache ): void
	{
		$this->_view_cache = $cache;
	}

	public function setVars( array $vars ): void
	{
		$this->_view_vars = $vars;
	}

	/**
	 * Assigns a variable which will be available in the templates
	 *
	 * @param string $var
	 * @param mixed  $value
	 */
	public function __set( string $var, mixed $value ): void
	{
		if( strpos( $var, 'plugin_' ) === 0 )
		{
			throw new ViewException( 'You are trying to assign a value on an attribute which is reserved to a filter' );
		}

		$this->_view_vars[$var] = $value;
	}

	/**
	 * Returns an assigned variable or null if the variable does not exist
	 *
	 * @param string $var
	 *
	 * @return mixed
	 */
	public function __get( string $var ): mixed
	{
		if( strpos( $var, 'plugin_' ) === 0 )
		{
			$var = substr( $var, 7 );

			if( empty( $this->_view_registeredfilters[$var]['instance'] ) )
			{
				$class = $this->_view_registeredfilters[$var]['class_name'];
				$this->_view_registeredfilters[$var]['instance'] = new $class;
			}

			return $this->_view_registeredfilters[$var]['instance'];
		}

		return isset( $this->_view_vars[$var] ) ? $this->_view_vars[$var] : null;
	}

	/**
	 * Checks to see if a certain variable has been assigned
	 *
	 * @param string $var
	 *
	 * @return bool
	 */
	public function __isset( string $var ): bool
	{
		return isset( $this->_view_vars[$var] );
	}

	/**
	 * Returns the output of the template
	 *
	 * @return string
	 *
	 * @see self::display
	 */
	public function render( string $template ): string
	{
		ob_start();
		$this->display( $template );
		$output = ob_get_clean();

		//reset current template
		$this->setCurrentTemplate( null );

		return $output;
	}

	/**
	 * Parses the template, if it's not already cached and executes it
	 *
	 * @param string $template Path to template
	 */
	public function display( ?string $template = null ): mixed
	{
		if( $this->current_template )
		{
			return false;
		}

		if( ! $template )
		{
			//get template automatic from controller
			$class = str_replace( array( 'APP\\Controller\\', '' ), array( '', '/' ), get_class( $this->controller ) );

			$class = str_replace( '\\', '/', $class );

			//check if folder exits
			if( ! is_dir( $this->_view_tpldir . '/' . $class ) )
			{
				throw new NotFoundException( "Template Folder doesn't exists: " . $this->_view_tpldir . '/' . $class );
			}

			$template = $class . '/' . $this->controller->current_method . '.php';

			//check if template exists based on the method called;
			if( ! is_file( $this->_view_tpldir . '/' . $class . '/' . $this->controller->current_method . '.php' ) )
			{
				throw new NotFoundException( "Template doesn't exists: " . $this->_view_tpldir . '/' . $class . '/' . $this->controller->current_method . '.php' );
			}
		}

		$event = new \RPC\Events\ViewRendering($this, $template);
		\RPC\Signal::getInstance()->dispatch($event);

		if ($event->isPropagationStopped()) {
			return '';
		}

		$this->setCurrentTemplate( $template );

		$view = $this;

		extract( $this->_view_vars );

		/*
			"require"-ing the php file so that the PHP code is ran within the
			local context, which will make the variables (previously extracted)
			available without using $this->
		*/
		require $this->getFilteredFile( $template );

		\RPC\Signal::getInstance()->dispatch(new \RPC\Events\ViewRendered($this, $template));

		return null;
	}

	/**
	 * Returns the path to the filtered template
	 *
	 * @return string
	 */
	public function getFilteredFile( string $template ): string
	{
		$file = $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $template;

		if( ! file_exists( $file ) )
		{
			throw new NotFoundException( 'File "' . $file . '" does not exist' );
		}

		if( ! $this->getCache()->get( $file, $template ) )
		{
			$this->_view_filters = array();

			foreach( $this->_view_registeredfilters as & $v )
			{
				if( ! $v['instance'] )
				{
					$class = $v['class_name'];
					$v['instance'] = new $class();
				}

				$this->addFilter( $v['instance'] );
			}

			$this->getCache()->set( $file, $this->filter( file_get_contents( $file ) ), $template );
		}

		return $this->getCache()->get( $file, $template );
	}

	public function getCurrentTemplate(): string
	{
		return $this->current_template;
	}

	public function setCurrentTemplate( ?string $tpl ): self
	{
		$this->current_template = $tpl;
		return $this;
	}

	public function setController( object $obj ): void
	{
		$this->controller = $obj;
	}

	/**
	 * Adds a new filter to the queue
	 *
	 * @param \RPC\View\Filter $filter
	 *
	 * @return self
	 */
	public function addFilter( \RPC\View\Filter $filter ): self
	{
		$this->_view_filters[] = $filter;

		return $this;
	}

	/**
	 * Removes a filter from the queue
	 *
	 * @param \RPC\View\Filter $filter
	 *
	 * @return \RPC\View
	 */
	public function removeFilter( \RPC\View\Filter $filter ): self
	{
		$key = array_search( $filter, $this->_view_filters );
		if( $key !== false )
		{
			unset( $this->_view_filters[$key] );
		}
		return $this;
	}

	/**
	 * Returns an array of previously loaded filters
	 *
	 * @return array
	 */
	public function getFilters(): array
	{
		return $this->_view_filters;
	}

	/**
	 * Filters the source code through all registered filters
	 *
	 * @param string $source
	 *
	 * @return string
	 */
	public function filter( string $source ): string
	{
		foreach( $this->_view_filters as $filter )
		{
			$source = $filter->filter( $source );
		}

		return $source;
	}


	public function newForm(): Form
	{
		return new \RPC\View\Filter\Form();
	}


	public function getError( string $id = '' ): string
	{
		if( isset( $this->_view_errors[$id] ) )
		{
			return $this->_view_errors[$id];
		}

		return '';
	}

	public function setErrors( array $errors = array() ): void
	{
		if( count( $errors ) )
		{
			$this->_view_errors = array_replace( $this->_view_errors, $errors );
		}
	}

}

?>
