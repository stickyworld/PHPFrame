<?php
/**
 * PHPFrame/Document/XMLDocument.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * XML Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_XMLDocument extends PHPFrame_Document
{
    /**
     * DOM Document Type object
     * 
     * @var DOMDocumentType
     */
    protected $doctype = null;
    /**
     * DOM Document object
     * 
     * @var DOMDocument
     */
    protected $dom = null;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @uses   DOMImplementation
     * @since  1.0 
     */
    public function __construct($mime="text/xml", $charset=null) 
    {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);
        
        // Acquire DOM object of HTML type
        $this->dom = new DOMDocument("1.0", $this->getCharset()); 
    }
    
    /**
     * Covert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str            = $this->dom->saveXML();
        $str           .= $this->getBody();
        
        $xml_beautifier = new XML_Beautifier();
        return $xml_beautifier->formatString($str);
        //return $this->indent($this->dom->saveXML().$this->getBody());
    }
    
    public function getDOM()
    {
        return $this->dom;
    }
    
    /**
     * Get DOM Document Type object
     * 
     * @access public
     * @return DOMDocumentType
     * @since  1.0
     */
    public function getDocType()
    {
        // Create new doc type object if we don't have one yet
        if (!($this->doctype instanceof DOMDocumentType)) {
             // Create doc type object
            $imp = new DOMImplementation;
            $this->doctype = $imp->createDocumentType($this->qualified_name);
        }
        
        return $this->doctype;
    }
    
    /**
     * Add node/tag
     * 
     * @param DOMNode|null $parent  The parent object to which we want to add the new node.
     * @param string  $name    The name of the new node or tag
     * @param array   $attrs   An assoc array containing attributes key/value pairs.
     * @param string  $content Text content of the node if any
     * 
     * @access public
     * @return DOMNode Returns a reference to the newly created node
     * @since  1.0
     */
    public function addNode($parent=null, $name, $attrs=array(), $content=null)
    {
        $new_node = $this->dom->createElement($name);
        
        if ($parent instanceof DOMNode) {
            $parent->appendChild($new_node);
        } else {
            $this->dom->appendChild($new_node);
        }

        // Add attributes if any
        if (is_array($attrs) && count($attrs) > 0) {
            foreach ($attrs as $key=>$value) {
                $this->addNodeAttr($new_node, $key, $value);
            }
        }
        
        // Add text content if any
        if (!is_null($content)) {
            $this->addNodeContent($new_node, $content);
        }
        
        return $new_node;
    }
    
    /**
     * Add an attribute to a given node
     * 
     * @param DOMNode $node       The node we want to add the attributes to.
     * @param string  $attr_name  The attribute name
     * @param string  $attr_value The value for the attribute if any.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addNodeAttr(DOMNode $node, $attr_name, $attr_value)
    {
        // Create attribute
        $attr = $this->dom->createAttribute($attr_name);
        
        // Add attribute value
        $value = $this->dom->createTextNode($attr_value);
        $attr->appendChild($value);
        
        // Append attribute to node
        $node->appendChild($attr);
    }
    
    /**
     * Add content to given node
     * 
     * @param DOMNode $node The node where to add the content text.
     * @param string  $str  The text to add to the node
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addNodeContent(DOMNode $node, $str)
    {
        $text_node = $this->dom->createTextNode($str);
        $node->appendChild($text_node);
    }
    
    /**
     * This method is used to turn inline XML into human-readable text
     * 
     * @param string $str The XML as string.
     * 
     * @access protected
     * @return string
     * @since  1.0
     * @deprecated This function is now replaced with XML_Beautifier
     */
    protected function indent($str)
    {
        $return_array = explode('>', $str);
        $depth        = -1;
        
        for ($i = 0; $i < count($return_array) - 1; $i++) {
            if(strpos($return_array[$i], "\n")!==false) {
                $return_array[$i] = trim($return_array[$i]);
            }
                    
            $end_tag = strpos($return_array[$i], "</");
            
            if ($end_tag !== false) {
                if ($end_tag != 0) {
                    $return_array[$i] = $this->padding($depth).$return_array[$i];
                    $depth--; 
                    $return_array[$i] = str_replace(
                        "</",
                        "\r\n".$this->padding($depth)."</",$return_array[$i]
                    );
                } else {
                    $depth--;
                    $return_array[$i] = $this->padding($depth).$return_array[$i];
                }
                
                $depth--;
            } else {
                $return_array[$i] = $this->padding($depth).$return_array[$i];
            }
            
            $return_array[$i] = $return_array[$i].">\r\n";
            $depth++;
        }
        
        return implode($return_array);
    }
    
    /**
     * This generates padding with specified depth
     * 
     * @param int $depth
     * 
     * @access protected
     * @return string
     * @since  1.0
     * @deprecated This function is now replaced with XML_Beautifier
     */
    protected function padding($depth)
    {
        $padding = '';
        
        for ($tabs=0; $tabs<$depth; $tabs++) {
            $padding .= '  ';
        }
        
        return $padding;    
    }
}
