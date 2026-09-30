<?php

namespace RPC;

use RPC\Exception\InvalidArgumentException;
use RPC\Registry;
use RPC\View;
use RPC\View\Cache;

/**
 * @property array|null $flash Flash message data
 */
class Controller
{
	public ?string $template = null;
	public ?string $current_method = null;
	public ?string $current_controller = null;

	protected array $vars = array();

	public ?\RPC\HTTP\Request $request = null;
	public ?\RPC\HTTP\Response $response = null;

	public function display( ?string $template = null ): void
	{
		$this->template = $template;
		$this->getView( true )->display( $template );
	}


	public function getView( bool $refresh_vars = false ): View
	{
		if( ! Registry::registered( 'view' ) )
		{
			$view = new \RPC\View( APP_PATH  . '/View', new Cache( CACHE_PATH . '/view' ) );
			$view->setController( $this );

			$this->vars['current_method'] 		= $this->current_method;
			$this->vars['current_controller'] 	= $this->current_controller;
			$this->vars['csrf_token']			= md5( 'general' ) . '_' . \RPC\Util::csrf( md5( 'general' ) );

			$view->setVars( $this->vars );


			Registry::set( 'view', $view );
		}

		if( $refresh_vars )
		{
			$view = Registry::get( 'view' );
			$view->setVars( $this->vars );

			Registry::set( 'view', $view );
		}

		return Registry::get( 'view' );
	}


	public function setErrors( array $errors = array() ): void
	{
		$this->getView()->setErrors( $errors );
	}


	public function param( ?string $name = null, mixed $default = null ): mixed
	{
		return $this->request->getParam( $name, $default );
	}

	public function redirect( string $url ): void
	{
		$this->response->redirect( $url );
	}


	public function json( mixed $data = array() ): void
	{
		$this->response->json( $data );
	}

	public function jsonSuccess( mixed $data = array() ): void
	{
		$this->response->jsonSuccess( $data );
	}

	public function jsonError( string $error_message = '', mixed $data = array() ): void
	{
		$this->response->jsonError( $error_message, $data );
	}

/**
	 * Assigns a variable which will be available in the templates
	 *
	 * @param string $var
	 * @param mixed  $value
	 */
	public function __set( string $var, mixed $value ): void
	{
		if( strpos( $var, 'template' ) === 0 )
		{
			throw new InvalidArgumentException( 'You are trying to assign a value on an attribute which is reserved to a template name' );
		}

		$this->vars[$var] = $value;
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
		return isset( $this->vars[$var] ) ? $this->vars[$var] : null;
	}

	public function flash( ?string $message = null, ?string $message_type = null, ?bool $persistent = null ): ?array
    {
    	if( $message )
    	{
    		$_SESSION['_FLASH_'][] = array( 'message' => $message, 'message_type' => $message_type, 'persistent' => ( $persistent ? 1 : 0 ) );
    		return null;
    	}
    	else
    	{
    		$messages = array();

	        if( isset( $_SESSION['_FLASH_'] ) )
	        {
	            foreach( $_SESSION['_FLASH_'] as $index => $msg )
	            {
	                if ( ! $msg['persistent'] )
	                {
	                    unset( $_SESSION['_FLASH_'][$index] );
	                }
	                $messages[] = $msg;
	            }
	        }

	        return $messages;
    	}
    }


}

?>
