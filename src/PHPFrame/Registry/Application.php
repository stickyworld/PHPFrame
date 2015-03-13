<?php
/**
 * PHPFrame/Registry/Application.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Application.php 1057 2014-06-02 11:51:42Z chrismcband@gmail.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Application Registry Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Registry_Application extends PHPFrame_Registry
{
    /**
     * Instance of itself in order to implement the singleton pattern
     * 
     * @var object of type PHPFrame_Application_FrontController
     */
    private static $_instance=null;
    /**
     * Path to the cache directory in filesystem
     * 
     * @var string
     */
    private $_path=null;
    /**
     * Path to the cache file name in filesystem
     * 
     * @var string
     */
    private $_cache_file=null;
    /**
     * Array containing keys that should be treated as readonly as far as client
     * code is concerned
     * 
     * @var array
     */
    private $_readonly=array("permissions", "components", "widgets");
    /**
     * An array to store application registry data set on runtime
     * 
     * @var array
     */
    private $_data=array();
    /**
     * A boolean to indicate whether the data has changed since it was last written to file
     * 
     * @var bool
     */
    private $_dirty=false;
    
    /**
     * Constructor
     * 
     * The constructor is declared "protected" to make sure that this class can only
     * be instantiated using the static method getInstance(), serving up always the same
     * instance that the class stores statically.
     * 
     * Yes, you have guessed right, this class is a "singleton".
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function __construct($path=null)
    {
        // Set path to cache file
        $this->_path = $path;
        $this->_cache_file = "application.registry";

        // Read data from cache
        if (is_file($this->getFilePath())) {
            $serialized_array = file_get_contents($this->getFilePath());
            $this->_data = unserialize($serialized_array);
        }
        else {
            $this->_rebuildAppRegistry();
        }
    }
    
    /**
     * Destructor
     * 
     * The destructor method will be called as soon as all references to a particular 
     * object are removed or when the object is explicitly destroyed or in any order 
     * in shutdown sequence.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __destruct()
    {
        if ($this->isDirty()) {
            try {
                // Write data to file
                $this->_writeToFile();
            } catch (Exception $e) {
                trigger_error($e->getMessage());
                exit;
            }
        }
    }
    
    public function __sleep()
    {
        $this->_dirty = null;
    }
    
    public function __wakeup()
    {
        $this->_dirty = false;
    }
    
    /**
     * Get Instance
     * 
     * @param string $path Path to cache directory. It only needs to be passed the first
     *                     time the method is callled.
     * 
     * @static
     * @access public
     * @return PHPFrame_Registry
     * @since  1.0
     */
    public static function getInstance($path='') 
    {
        $path = (string) $path;

        if (!isset(self::$_instance)) {
            self::$_instance = new self($path);
        }
        
        return self::$_instance;
    }
    
    /**
     * Get an application registry variable
     * 
     * @param string $key
     * @param mixed  $default_value
     * 
     * @access public
     * @return mixed
     * @since  1.0
     */
    public function get($key, $default_value=null) 
    {
        $this->_checkHealthy();

        // Set default value if appropriate
        if (!isset($this->_data[$key]) && !is_null($default_value)) {
            $this->_data[$key] = $default_value;
            
            // Mark data as dirty
            $this->markDirty();
        }
        
        // Return null if index is not defined
        if (!isset($this->_data[$key])) {
            return null;
        }
        
        return $this->_data[$key];
    }
    
    /**
     * Set an application registry variable
     * 
     * @param string $key
     * @param mixed  $value
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function set($key, $value) 
    {
        if (array_key_exists($key, $this->_readonly)) {
            $msg = "Tried to set a read-only key (";
            $msg .= $key.") in Application Registry.";
            throw new PHPFrame_Exception($msg);
        }
        
        $this->_data[$key] = $value;
        
        // Mark data as dirty
        $this->markDirty();
    }
    
    /**
     * Get full path to cache file
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getFilePath()
    {
        return $this->_path.DS.$this->_cache_file;
    }
    
//    /**
//     * Get Permissions object
//     * 
//     * @access public
//     * @return PHPFrame_Application_Permissions
//     * @since  1.0
//     */
//    public function getPermissions() 
//    {
//        return $this->_data['permissions'];
//    }
    
    /**
     * Get Comonents object
     * 
     * @access public
     * @return PHPFrame_Application_Components
     * @since  1.0
     */
    public function getComponents() 
    {
        return $this->_data['components'];
    }
    
    /**
     * Get Widgets object
     * 
     * @access public
     * @return PHPFrame_Application_Widgets
     * @since  1.0
     */
    public function getWidgets() 
    {
        return $this->_data['widgets'];
    }
    
    /**
     * Mark the application data as dirty (it needs writting to file)
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    public function markDirty()
    {
        $this->_dirty = true;
    }
    
    /**
     * Is the application registry data dirty?
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function isDirty()
    {
        return $this->_dirty;
    }
    
    /**
     * Write application registry to file
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _writeToFile()
    {
        // Ensure that cache dir is writable
        PHPFrame_Utils_Filesystem::ensureWritableDir($this->_path);
        
        // Store data in cache file
        $data = serialize($this->_data);
        PHPFrame_Utils_Filesystem::write($this->getFilePath(), $data);
    }

    /**
     * Checks if data has been populated, if data is found to be boolean false,
     * unserialize is assumed to have failed and app registry will be rebuilt
     *
     * @access private
     * @return void
     * @since 1.0
     */
    private function _checkHealthy()
    {
        //check _data exists, if data is false, unserialize failed, recreate the app registry
        if ($this->_data === FALSE) {
            $this->_rebuildAppRegistry();
        }
    }

    /**
     * Rebuilds the application registry, populating it with components and widgets
     *
     * @access private
     * @return void
     * @since 1.0
     */
    private function _rebuildAppRegistry()
    {
        // Rebuild app registry
        //$permissions = new PHPFrame_Application_Permissions();
        $components = new PHPFrame_Application_Components();
        $widgets = new PHPFrame_Application_Widgets();

        // Store objects in App Regsitry
        //$this->set("permissions", $permissions);
        $this->set("components", $components);
        $this->set("widgets", $widgets);
    }
}
