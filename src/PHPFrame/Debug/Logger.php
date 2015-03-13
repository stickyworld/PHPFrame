<?php
/**
 * PHPFrame/Debug/Logger.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Debug
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Logger.php 927 2010-01-12 19:48:12Z chrismcband $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Logger Class
 * 
 * This class implements the "Observer" base class in order to subscribe to updates
 * from "observable" objects (objects of type PHPFrame_Base_Subject).
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Debug
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Base_Observer
 * @since      1.0
 */
class PHPFrame_Debug_Logger extends PHPFrame_Base_Observer
{   
    /**
     * Constructor
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct()
    {
        // Check path is valid
        // ...
    }
    
    /**
     * Handle observed objects updates
     * 
     * @param SplSubject $subject The subjuct issuing the update
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function doUpdate(SplSubject $subject)
    {
        if ($subject instanceof PHPFrame_Database) {
            
        } else {
            
        }
    }
    
    /**
     * Write string to log file
     * 
     * @param string $str The string to append to log file
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function write($str) 
    {
        // Add log separator
        $info = "\n\n---\n";
		
		// Add date and time
        $info .= "[".date("Y-m-d H:i:s")."]";
		
		// Add IP address if available
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $info .= " [ip:".$_SERVER['REMOTE_ADDR']."]";
        } else {
            $info .= " [cli]";
        }
        
        // Write log to filesystem using PHPFrame's utility class
        if (defined("PHPFRAME_VAR_DIR")) {
            $log_dir = PHPFRAME_VAR_DIR;
        } else {
            require_once "PEAR/Config.php";
			$log_dir = PEAR_Config::singleton()->get('data_dir');
			$log_dir .= DS."PHPFrame";
        }
        
        $log_file = $log_dir.DS."log";
        if (file_exists($log_file)){
	        $log_permissions = fileperms($log_file);
	        $log_permissions = substr(sprintf("%o", $log_permissions), -4);
	        $matches = array();
	        preg_match('/0[0-9]([0-9])[0-9]/', $log_permissions, $matches);
	        
	        if ($matches[1]==4){
	        	chmod($log_file, 0664);
	        }
        }
        
        PHPFrame_Utils_Filesystem::write($log_file, $info.$str, true);
    }
}
