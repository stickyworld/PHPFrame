<?php
/**
 * PHPFrame/Debug/Informer.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Debug
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Informer.php 111 2009-06-26 12:31:37Z luis.montero@e-noise.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Informer Class
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
class PHPFrame_Debug_Informer extends PHPFrame_Base_Observer
{
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
        // ...
    }
    
}