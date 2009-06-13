<?php
/**
 * PHPFrame/Application/Modules.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Modules Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_Modules
{
    /**
     * Array containing the installed modules
     * 
     * @var array
     */
    private $_array=array();
    
    /**
     * Constructor
     * 
     * @return void
     * @since  1.0
     */
    function __construct() 
    {
        $query = "SELECT m.*, mo.option AS `option` FROM #__modules AS m ";
        $query .= " LEFT JOIN #__modules_options mo ON mo.moduleid = m.id ";
        $query .= " ORDER BY m.ordering ASC";
        $this->_array = PHPFrame::getDB()->loadObjectList($query);
    }
    
    /**
     * Count the number of modules assigned to the given position in the current component option.
     * 
     * @param string $position
     * 
     * @return int
     * @since  1.0
     */
    function countModules($position) 
    {
        $count = 0;
        
        foreach ($this->_array as $module) {
            if ($module->position == $position 
                && $module->enabled == 1
                && ($module->option == PHPFrame::Request()->getComponentName() || $module->option == "*")
                ) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Display modules
     * 
     * This method displays modules assigned to a named position depending on 
     * whether the are also assigned to the current component.
     * 
     * @param string $position
     * @param string $class_suffix
     * 
     * @return mixed
     * @since  1.0
     */
    function display($position, $class_suffix='') 
    {
        $output = array();
        
        foreach ($this->_array as $module) {
            if ($module->position == $position 
                && $module->enabled == 1
                && ($module->option == PHPFrame::Request()->getComponentName() || $module->option == "*")
                ) {
                $module_file_path = _ABS_PATH.DS."src".DS."modules".DS."mod_".$module->name.DS."mod_".$module->name.".php";
                if (file_exists($module_file_path)) {
                    // Start buffering
                    ob_start();
                    require_once $module_file_path;
                    // save buffer
                    $output[] = ob_get_contents();
                    // clean output buffer
                    ob_end_clean();
                }
                else {
                    throw new PHPFrame_Exception('Module file '.$module_file_path.' not found.');
                }
            }
        }
            
        // prepare html output and filter out empty modules
        $html = '';
        for ($i=0; $i<count($output); $i++) {
            $output[$i] = trim($output[$i]);
            if (!empty($output[$i])) {
                $html .= '<div class="module'.$class_suffix.'">';
                $html .= $output[$i];
                $html .= '</div>';
            }
        }
            
        return $html;
    }
}
