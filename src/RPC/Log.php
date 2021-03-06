<?php

namespace RPC;

/**
 * Logging Class
 *
 * @package		RPC
 * @category	Logging
 * @author		Three29
 * @link		http://codeigniter.com/user_guide/general/errors.html
 */

class Log {

	var $log_path;
	var $_threshold = 1;
	var $_date_fmt  = 'Y-m-d H:i:s A';
	var $_enabled   = true;
	var $_log_to_file = true;
	var $_levels       = array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		if( ! getenv( 'LOGS_ENABLED' ) )
		{
			$this->_enabled = false;
			return false;
		}

		if ( ! getenv( 'LOG_TO_FILE' ) )
		{
			$this->_log_to_file = false;
		}

		if ( getenv( 'LOG_PATH' ) )
		{
			$this->log_path = getenv( 'LOG_PATH' );
		}
		else
		{
			$this->log_path = ROOT_PATH . '/logs/';
		}


		if ( ! is_dir($this->log_path) || ! is_writable($this->log_path))
		{
			$this->_enabled = false;
		}

		if( getenv( "LOG_THRESHOLD" ) )
		{
			$this->_threshold = getenv( "LOG_THRESHOLD" );
		}

		if( getenv( "LOG_DATE_FORMAT" ) )
		{
			$this->_date_fmt = getenv( "LOG_DATE_FORMAT" );
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @access	public
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */
	function write_log( $level = 'error', $msg, $php_error = false )
	{
		if ($this->_enabled === false)
		{
			return false;
		}

		$level = strtoupper($level);

		if ( ! isset($this->_levels[$level]) || ($this->_levels[$level] > $this->_threshold))
		{
			return false;
		}

		//write to globals
		if ( ! isset( $GLOBALS['logs'] ) )
		{
			$GLOBALS['logs'] = array();
		}

		$GLOBALS['logs'][] = $level . '  --> ' . $msg;

		if ( $this->_log_to_file )
		{

			$filepath = $this->log_path . 'log-' . date('Y-m-d') . '.txt';
			$message  = '';

			if ( ! file_exists($filepath))
			{
				//$message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
			}

			if ( ! $fp = fopen($filepath, 'ab'))
			{
				return false;
			}

			$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";



			flock($fp, LOCK_EX);
			fwrite($fp, $message);
			flock($fp, LOCK_UN);
			fclose($fp);

			chmod($filepath, 0777);
		}

		return true;
	}

}
// END Log Class

/* End of file Log.php */
