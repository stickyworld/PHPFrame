<?php
/**
 * PHPFrame/Application/Response.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Response.php 71 2009-06-14 11:54:03Z luis.montero@e-noise.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Response Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_Response
{
    /**
     * Instance of itself
     * 
     * @var PHPFrame_Application_Response
     */
    private static $_instance=null;
    /**
     * HTTP Response code
     * 
     * @var int
     */
    private $_code=null;
    /**
     * An array containing the raw headers
     * 
     * @var array
     */
    private $_header=array();
    /**
     * The document object used by this response
     * 
     * @var PHPFrame_Document
     */
    private $_document=null;
    
    /**
     * Constructor
     * 
     * @return void
     */
    private function __construct()
    {
        // ...
    }
    
    /**
     * Get instance
     * 
     * @return PHPFrame_Application_Response
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    /**
     * Set response content.
     * 
     * @param PHPFrame_Document $document
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setDocument(PHPFrame_Document $document) 
    {
        $this->_document = $document;
    }
    
    /**
     * Get response content
     * 
     * @access public
     * @return PHPFrame_Document
     * @since  1.0
     */
    public function getDocument()
    {
        return $this->_document;
    }
    
    /**
     * Set header line
     * 
     * @param string $line The header line
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setHeader($line) 
    {
        $this->_header[] = $line;
    }
    
    /**
     * Set the document title held by this response.
     * 
     * @param string $str The title string
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setTitle($str)
    {
        $this->_document->setTitle($str);
    }
    
    /**
     * Render view and store in document's body
     * 
     * This method is invoked by the views and renders the ouput data in the
     * document specific format.
     * 
     * @param PHPFrame_MVC_View $view The view object to process.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
     public function renderView(PHPFrame_MVC_View $view)
     {
         $this->_document->renderView($view);
         $this->send();
     }
     
    /**
     * Send response back to client
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function send() 
    {
        //var_dump($this); exit;
        foreach ($this->_header as $line) {
            header($line);
        }
		
        echo $this->_document;
        
        if (config::DEBUG) {
            echo '<pre>'.PHPFrame_Debug_Profiler::getReport().'</pre>';
        }
    }
}