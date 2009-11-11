<?php
/**
 * PHPFrame/Registry/FileRegistry.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Registry
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * File based registry
 * 
 * @category PHPFrame
 * @package  Registry
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Registry
 * @uses     PHPFrame_Permissions, PHPFrame_Libraries, 
 *           PHPFrame_Features, PHPFrame_Filesystem
 * @since    1.0
 */
class PHPFrame_FileRegistry extends PHPFrame_Registry
{
    /**
     * PHPFrame_FileObject object representing the cache file on disk
     * 
     * @var PHPFrame_FileObject
     */
    private $_file_obj = null;
    /**
     * An array to store application registry data set on runtime
     * 
     * @var array
     */
    private $_data = array();
    /**
     * A boolean to indicate whether the data has changed since it was last 
     * written to file
     * 
     * @var bool
     */
    private $_dirty = false;
    
    /**
     * Constructor
     * 
     * @param string $cache_file
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($cache_file) 
    {
        $cache_file = trim((string) $cache_file);
        
        // Read data from cache
        if (is_file($cache_file)) {
            // Open cache file in read/write mode
            $this->_file_obj = new PHPFrame_FileObject($cache_file, "r+");
            // Load data from cache file
            $this->_data = unserialize($this->_file_obj->getFileContents());
        } else {
            // Open cache file in write mode
            $this->_file_obj = new PHPFrame_FileObject($cache_file, "w");
        }
    }
    
    /**
     * Destructor
     * 
     * The destructor method will be called as soon as all references to a 
     * particular object are removed or when the object is explicitly destroyed 
     * or in any order in shutdown sequence.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __destruct()
    {
        if ($this->isDirty()) {
            $this->_file_obj->rewind();
            $this->_file_obj->fwrite(serialize($this->_data));
        }
    }
    
    /**
     * Implementation of IteratorAggregate interface
     * 
     * @access public
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
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
        $key = trim((string) $key);
        
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
        $key = trim((string) $key);
        
        $this->_data[$key] = $value;
        
        // Mark data as dirty
        $this->markDirty();
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
}
