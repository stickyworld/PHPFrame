<?php
/**
 * PHPFrame/Mail/Mailer.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mail
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Mailer.php 1026 2012-03-26 17:54:16Z chrismcband $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Mailer Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mail
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Mail_Mailer extends PHPMailer
{
    private $_messageid_sfx=null;
    private $_error=array();
    
    /**
     * Constructor
     * 
     * Initialise some PHPMailer default values
     * 
     * @return    void
     * @since    1.0
     */
    public function __construct() 
    {
        $this->Mailer = PHPFrame::Config()->get("MAILER");
        $this->Host = PHPFrame::Config()->get("SMTP_HOST");
        $this->Port = PHPFrame::Config()->get("SMTP_PORT");
        $this->CharSet = "UTF-8";
        $this->SMTPAuth = PHPFrame::Config()->get("SMTP_AUTH");
        $this->Username = PHPFrame::Config()->get("SMTP_USER");
        $this->Password = PHPFrame::Config()->get("SMTP_PASSWORD");
        $this->From = PHPFrame::Config()->get("FROMADDRESS");
        $this->FromName = PHPFrame::Config()->get("FROMNAME");
        
        // Sets the hostname to use in Message-Id and Received headers and as default HELO string. 
        // If empty, the value returned by SERVER_NAME is used or 'localhost.localdomain'.
        $this->Hostname = PHPFrame::Config()->get("SMTP_HOST");
    }
    
    /**
     * This method allows to add a suffix to the message id.
     * 
     * This can be very useful when adding data to the message id for processing of replies.
     * 
     * The suffix is added to the the headers in $this->CreateHeader() and is encoded in base64.
     * 
     * @param    string    $str
     * @return    void
     */
    public function setMessageIdSuffix($str) 
    {
        $this->_messageid_sfx = (string)$str;
    }
    
    /**
     * Get the message id suffix.
     * 
     * @return    string
     */
    public function getMessageIdSuffix() 
    {
        return $this->_messageid_sfx;
    }
    
    /**
     * This method overrides the parent CreateHeader() method.
     * 
     * This method appends the message id suffix encoded in base64.
     * 
     * @see     src/lib/phpmailer/PHPMailer#CreateHeader()
     * @return    string
     */
    public function CreateHeader() 
    {
        $result = parent::CreateHeader();
        
        if (!is_null($this->_messageid_sfx)) {
            $pattern = "/Message\-Id\: <([a-zA-Z0-9]+)@/i";
            $replacement = "Message-Id: <$1-".base64_encode($this->_messageid_sfx)."@";
            $result = preg_replace($pattern, $replacement, $result);
        }
        
        return $result;
    }
}
