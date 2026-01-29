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

	public string $log_path;
	public int $_threshold = 1;
	public string $_date_fmt  = 'Y-m-d H:i:s A';
	public bool $_enabled   = true;
	public bool $_log_to_file = true;
	public array $_levels       = array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		$root_path = \RPC\Registry::get('root_path');
		if( ! env( 'LOGS_ENABLED' ) )
		{
			$this->_enabled = false;
			return;
		}

		if ( ! env( 'LOG_TO_FILE' ) )
		{
			$this->_log_to_file = false;
		}

		$logPath = env( 'LOG_PATH' );
		if ( $logPath )
		{
			$this->log_path = $logPath;
		}
		else
		{
			$this->log_path = $root_path . '/logs/';
		}


		if ( ! is_dir($this->log_path) || ! is_writable($this->log_path))
		{
			$this->_enabled = false;
		}

		$threshold = env( "LOG_THRESHOLD" );
		if( $threshold )
		{
			$this->_threshold = (int) $threshold;
		}

		$dateFormat = env( "LOG_DATE_FORMAT" );
		if( $dateFormat )
		{
			$this->_date_fmt = $dateFormat;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @access	public
	 * @param	string $msg	the error message
	 * @param	string $level	the error level
	 * @param	bool $php_error	whether the error is a native PHP error
	 * @return	bool
	 */
	function write_log( string $msg, string $level = 'error', bool $php_error = false ): bool
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
