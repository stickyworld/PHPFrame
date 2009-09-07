<?php
/**
 * PHPFrame/Mapper/DomainObjectCollection.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Collection Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_DomainObjectCollection implements Iterator, Countable
{
    /**
     * A domain factory object used to create objects in collection
     * 
     * @var PHPFrame_DomainObjectFactory
     */
    private $_obj_fact;
    /**
     * Raw array used to generate domain objects
     * 
     * @var array
     */
    private $_raw;
    /**
     * The total number of elements in the collection
     * 
     * @var int
     */
    private $_total;
    /**
     * Internal array pointer
     * 
     * @var int
     */
    private $_pointer=0;
    /**
     * Storage array used to manage the collection's objects
     * 
     * @var array;
     */
    private $_objects=array();
    
    /**
     * Constructor
     * 
     * @param array                               $raw
     * @param PHPFrame_DomainObjectFactory $obj_factory
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(
        array $raw=null, 
        PHPFrame_DomainObjectFactory $obj_factory=null
    ) {
        if (!is_null($raw) && !is_null($obj_factory)) {
            // If the raw array is only one level of depth we assume it is 
            // only one element and we wrap it in an array to make is a collection 
            // of a single entry
            $array_obj = new PHPFrame_Array($raw);
            
            if ($array_obj->depth() == 1) {
                $raw = array($raw);
            }
            
            $this->_raw = $raw;
            $this->_total = count($raw);
        }
        
        $this->_obj_fact = $obj_factory;
    }
    
    /**
     * Get domain object at given key
     * 
     * @param string $key
     * 
     * @access public
     * @return PHPFrame_DomainObject
     * @since  1.0
     */
    public function getElement($key)
    {
        if ($key >= $this->count() || $key < 0) {
            return null;   
        }
        
        if (isset($this->_objects[$key])) {
            return $this->_objects[$key];
        }
        
        if (isset($this->_raw[$key])) {
            $this->_objects[$key] = $this->_obj_fact->createObject($this->_raw[$key]);
            return $this->_objects[$key];
        }
    }
    
    /**
     * Add domain object to the collection
     * 
     * @param PHPFrame_DomainObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addElement(PHPFrame_DomainObject $obj)
    {
        if (in_array($obj, $this->_objects)) {
            return;
        }
        
        $this->_objects[$this->_total++] = $obj;
    }
    
    /**
     * Remove domain object from the collection
     * 
     * @param PHPFrame_DomainObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function removeElement(PHPFrame_DomainObject $obj)
    {
        if (!in_array($obj, $this->_objects)) {
            return;
        }
        
        $key = array_keys($this->_objects, $obj);
        unset($this->_objects[$key]);
    }
    
    /**
     * Implementation of Iterator::current()
     * 
     * @access public
     * @return PHPFrame_DatabaseRow
     * @since  1.0
     */
    public function current() 
    {
        return $this->getElement($this->key());
    }
    
    /**
     * Implementation of Iterator::next()
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function next() 
    {
        $this->_pointer++;
    }
    
    /**
     * Implementation of Iterator::key()
     *   
     * @access public
     * @return int
     * @since  1.0
     */
    public function key() 
    {
        return $this->_pointer;
    }
    
    /**
     * Implementation of Iterator::valid()
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function valid() 
    {
        return ($this->key() < $this->count());
    }
    
    /**
     * Implementation of Iterator::rewind()
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function rewind() 
    {
        $this->_pointer = 0;
    }
    
    public function count()
    {
        return $this->_total;
    }
}