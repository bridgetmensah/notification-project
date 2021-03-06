<?php 
defined('COREPATH') or exit('No direct script access allowed');

/**
 * Base_Log class
 * Customized to use new error levels
 */
class Base_Log extends \CI_Log {

    /**
     * Override the $_levels array to 
     * allow custom levels
     *
     * @var array
     */
    public $_levels = [	
		'USER' => '1',
		'APP' => '2',
		'ERROR' => '3',
		'INFO' => '4',  
		'DEBUG' => '5',  
		'ALL' => '6'
	];
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
	 * Write Log File
	 *
	 * Generally this function will be called 
     * using the global log_message() function
	 *
	 * @param	string	$level 	The error level: 'error', 'debug' or 'info'
	 * @param	string	$msg 	The error message
	 * @return	bool
	 */
	public function write_log($level, $msg)
	{
		if ($this->_enabled === false) {
			return false;
		}

		$level = strtoupper($level);

        if (( 
            ! isset($this->_levels[$level]) 
            OR ($this->_levels[$level] > $this->_threshold))
            && ! isset($this->_threshold_array[$this->_levels[$level]])
        ) {
			return false;
		}

		// $filepath = $this->_log_path.'log-'.date('Y-m-d').'.'.$this->_file_ext;

		$filepath = $this->log_path($level);

		$message = '';

		if ( ! file_exists($filepath)) {
			$newfile = true;
			// Only add protection to php files
			if ($this->_file_ext === 'php') {
				$message .= "<?php defined('COREPATH') or exit('No direct script access allowed'); ?>\n\n";
			}
		}

		if ( ! $fp = @fopen($filepath, 'ab')) {
			return false;
		}

		flock($fp, LOCK_EX);

		// Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
		if (strpos($this->_date_fmt, 'u') !== false) {
			$microtime_full = microtime(true);
			$microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
			$date = new DateTime(date('Y-m-d H:i:s.'.$microtime_short, $microtime_full));
			$date = $date->format($this->_date_fmt);
		} else {
			$date = date($this->_date_fmt);
		}

		$message .= $this->_format_line($level, $date, $msg);

		for ($written = 0, $length = self::strlen($message); $written < $length; $written += $result) {
			if (($result = fwrite($fp, self::substr($message, $written))) === false) {
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		if (isset($newfile) && $newfile === true) {
			chmod($filepath, $this->_file_permissions);
		}

		return is_int($result);
	}
   
    /**
     * Change log_path based on error level
     *
     * @param string $level
     * @return string
     */
    private function log_path($level) 
	{
		$filepath = $this->_log_path.'log-'.date('Y-m-d').'.'.$this->_file_ext;

		if ($level === 'APP') {
			$filepath = APP_LOG_PATH.strtolower($level).'-log-'.date('Y-m-d').'.'.$this->_file_ext;
		}

		if ($level === 'USER') {
			$filepath = APP_LOG_PATH.strtolower($level).'-log-'.date('Y-m-d').'.'.$this->_file_ext;
		}

		return $filepath;
	}

}
/* end of file Base_Log.php */
