<?php
/**
 * data/CLITool/src/controllers/scaffold.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Scaffold controller.
 * 
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class ScaffoldController extends PHPFrame_ActionController
{
    /**
     * Absolute path to installation directry.
     * 
     * @var string
     */
    private $_install_dir=null;
    
    /**
     * Constructor
     * 
     * @param string $install_dir
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($install_dir=null)
    {
        if (is_null($install_dir)) {
            $this->_install_dir = getcwd();
        } else {
            $this->_install_dir = trim((string) $install_dir);
        }
        
        parent::__construct("usage");
    }
    
    /**
     * Display controller's usage instructions
     * 
     * @return void
     * @since  1.0
     */
    public function usage()
    {
        $doc = new PHPFrame_ControllerDoc(new ReflectionClass(get_class($this)));
        
        $this->response()->setTitle("Usage instructions");
        $this->response()->setBody((string) $doc);
    }
    
    /**
     * Create a database table for a given PersistentObject class.
     *  
     * @param string $path        [Optional] Path to file or directory  
     *                            containing the persistent objects. If  
     *                            ommitted the default 'src/models' directory 
     *                            will be scanned.
     * @param bool   $drop        [Optional] Default value is FALSE. When set 
     *                            to true existing table will be dropped.
     * 
     * @return void
     * @since  1.0
     */
    public function create_table($path="", $drop=false)
    {
    	$path = trim((string) $path);
    	
    	// Prepend path to models if relative path is passed
    	if (!preg_match("/^\//", $path)) {
    	    $path = $this->_install_dir.DS."src".DS."models".DS.$path;
    	}
        
        if (is_dir($path)) {
            $dir_it    = new RecursiveDirectoryIterator($path);
            $flat_it   = new RecursiveIteratorIterator($dir_it);
            $filter_it = new RegexIterator($flat_it, '/\.php$/');
            
            foreach ($filter_it as $file) {
                require_once $file->getRealPath();
            }
        } elseif (is_file($path)) {
           require_once $path;
        } else {
            $msg  = "Could not find any PHP files to search for persistent ";
            $msg .= "objects.";
            throw new UnexpectedValueException($msg);
        }
        
        $declared_classes = get_declared_classes();
        
        // We get the key of the current class in the get_declared_classes() 
        // array in order to detect new declared classes after including the
        // php files
        $class_key = array_keys($declared_classes, get_class($this));
        $class_key = $class_key[0];
        
        $objs = array();
        for ($i=($class_key+1); $i<count($declared_classes); $i++) {
            $reflection_obj = new ReflectionClass($declared_classes[$i]);
            if ($reflection_obj->isSubclassOf("PHPFrame_PersistentObject")) {
                $objs[] = $reflection_obj->newInstance();
            }
        }
        
        // Get database options from config file
        $config_file = $this->_install_dir.DS."etc".DS."phpframe.ini";
        $config      = new PHPFrame_Config($config_file);
        $options     = $config->getSection("db");
        
        if (
            strtolower($options["driver"]) == "sqlite" 
            && !preg_match("/^\//", $options["name"])
        ) {
            $options["name"] = $this->_install_dir.DS."var".DS.$options["name"];
        }
        
        $db         = PHPFrame_DatabaseFactory::getDB($options);
        $or_toolbox = new PHPFrame_ObjectRelationalToolbox();
        
        foreach ($objs as $obj) {
            $table_name = get_class($obj);
            
            if (isset($options["prefix"]) && !empty($options["prefix"])) {
                $table_name = $options["prefix"].$table_name;
            }
            
            $or_toolbox->createTable($db, $obj, $table_name, $drop);
        }
        
        $this->notifySuccess("Database table successfully created.");
    }
    
    /**
     * Create a persistent object class file based on a database table.
     * 
     * @return void
     * @since  1.0
     * @todo   Method needs implementation...
     */
    public function create_persistent_object()
    {
        
    }
    
    /**
     * Create a files for a new feature...
     * 
     * @return void
     * @since  1.0
     * @todo   Method needs implementation...
     */
    public function create_feature()
    {
        
    }
    
    /**
     * Create a template controller class.
     * 
     * @return void
     * @since  1.0
     * @todo   Method needs implementation...
     */
    public function create_controller()
    {
        
    }
}