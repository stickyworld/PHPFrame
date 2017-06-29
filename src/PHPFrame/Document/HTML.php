<?php
/**
 * PHPFrame/Document/HTML.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Document
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: HTML.php 1055 2014-05-21 12:17:56Z chrismcband@gmail.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * HTML Document Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Document
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Document
 * @since      1.0
 */
class PHPFrame_Document_HTML extends PHPFrame_Document_XML
{
    /**
     * The qualified name of the document type to create. 
     * 
     * @var string
     */
    protected $qualified_name="html";
    /**
     * Pathway object
     * 
     * @var PHPFrame_Application_Pathway
     */
    private $_pathway=null;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @uses   DOMImplementation, PHPFrame_Utils_URI, PHPFrame_Application_Pathway
     * @since  1.0 
     */
    public function __construct($mime="text/html", $charset=null) 
    {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);
        
        // Acquire DOM object of HTML type
        $imp = new DOMImplementation;
        $this->dom = $imp->createDocument(null, 
                                           $this->qualified_name, 
                                           $this->getDocType()); 
        
        // Get root node
        $html_node = $this->dom->getElementsByTagName("html")->item(0);
        
        // Add head
        $head_node = $this->addNode($html_node, "head");
        // Add body
        $this->addNode($html_node, "body", null, "{content}");
        
        // Add meta tags
        $this->addMetaTag("generator", "PHPFrame");
        //add html5 charset
        $head_node = $this->dom->getElementsByTagName("head")->item(0);
        $charset_node = $this->addNode($head_node, "meta");
        $this->addNodeAttr($charset_node, "charset", $this->charset);

        // Add base url
        $uri = new PHPFrame_Utils_URI();
        $this->addNode($head_node, "base", array("href"=>$uri->getBase()));
        
        // Acquire a pathway object used by this document
        $this->_pathway = new PHPFrame_Application_Pathway();
    }
    
    public function setBody($str)
    {
        $this->body = (string) $str;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Render view and store in document's body
     * 
     * This method is invoked by the views and renders the ouput data in the
     * document specific format.
     * 
     * @param PHPFrame_MVC_View $view        The view object to process.
     * @param bool              $apply_theme Boolean to insicate whether we want to apply 
     *                                       the overall theme or not.
     * 
     * @access public
     * @return void
     * @since  1.0
     * @todo It is very important to check path used for require_once call for security.
     */
    public function render(PHPFrame_MVC_View $view, $apply_theme=true) 
    {
        $component_path = PHPFRAME_INSTALL_DIR.DS."src";
        $component_path .= DS."components".DS.$view->getComponentName();
        
        $tmpl_path = $component_path.DS."views";
        $tmpl_path .= DS.$view->getName().DS."tmpl";
        
        // Add client specific template to path
        $tmpl_path .= DS.PHPFrame::Session()->getClientName();
        
        $layout = $view->getLayout();
        if ($layout) {
            $tmpl_path .= "_".$layout;
        }
        
        $tmpl_path .= ".php";
        
        if (is_file($tmpl_path)) {
            // Start buffering
            ob_start();
            // set view data as local array
            $data = $view->getData();
            // Include template file
            require_once $tmpl_path;
            // save buffer in body property
            $this->body = ob_get_contents();
            // clean output buffer
            ob_end_clean();
        } else {
            throw new PHPFrame_Exception("Layout template file ".$tmpl_path." not found.");
        }

        //add facebook xfbml script if specified in config
        $facebook_integration = PHPFrame::Config()->get('FACEBOOK_INTEGRATION');
        if ($facebook_integration == 1) {
            // Get root node
            $html_node = $this->dom->getElementsByTagName("html")->item(0);
            $attr_ns = $this->dom->createAttributeNS( 'http://ogp.me/ns/fb#', 'fb:like' );
        }
        
        if ($apply_theme) {
            $this->_applyTheme($view);
        } else {
            // we dont need to wrap the component output in the overall template
            // so we just prepend the sytem events and return
//            $widgets = PHPFrame::AppRegistry()->getWidgets();
//            $sys_events = $widgets->display('sysevents', '_sysevents');
//            $this->body = $sys_events.$this->body;
            return;
        }
    }
    
    public function renderPathway(PHPFrame_Application_Pathway $pathway)
    {
        $array = $pathway->toArray();
        
        $html = '<div class="pathway">';
        for ($i=0; $i<count($array); $i++) {
            if ($i>0) {
                $html .= ' &gt;&gt; ';
            }
            $html .= '<span class="pathway_item">';
            if (!empty($array[$i]['url']) && $i < (count($array))-1) {
                $html .= '<a href="'.$array[$i]['url'].'">'.$array[$i]['title'].'</a>';
            } else {
                $html .= $array[$i]['title'];
            }
            $html .= '</span>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Method used to render Row Collections in HTML format
     * 
     * @param PHPFrame_Database_RowCollection
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function renderRowCollection(
        PHPFrame_Database_RowCollection $collection, 
        $headings=null
    ) {
        // Build table to display row data
        $html = "<table class=\"data_list\" width=\"100%\">\n";
        
        // Prepare heading array
        if (!is_null($headings) && !is_array($headings)) {
            $msg = "Wrong data type.";
            $msg .= "Headings must be passed as an array.";
            throw new PHPFrame_Exception($msg);
        } elseif (is_null($headings)) {
            // If no specified headings we get keys from collection
            $headings = $collection->getKeys();
        }
        
        // Print headings
        $html .= "<thead>\n<tr>\n";
        foreach ($headings as $heading) {
            $html .= "<th>".$heading."</th>\n";
        }
        $html .= "</tr>\n</thead>\n";
        
        // Print tbody
        $html .= "<tbody>\n";
        foreach ($collection as $row) {
            $html .= "<tr>\n";
            foreach ($row->getKeys() as $key) {
                $html .= "<td>".$row->$key."</td>\n";
            }
            $html .= "</tr>\n";
        }
        $html .= "</tbody>\n";
        $html .= "</table>";
        
        return $html;
    }
    
    /**
     * Render HTML filter for row collection
     * 
     * This method builds an HTML string with UI filtering elements to be used with
     * row collection objects.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function renderRowCollectionFilter(PHPFrame_Database_RowCollection $collection, $ajax_container='',
        $pagination=true, $search=true, $limits=array(5, 10, 25, 50, 100))
    {
        $html = '<div>';
        if ($pagination){
	        // Print form to select limit per page
	        $html .= '<div class="subset_limit">';
	        $html .= '<form name="limitform" id="limitform" method="post">';
	        $html .= 'Display: ';
	        $html .= '<select name="limit" onchange="document.forms[\'limitform\'].submit();">';

            $found = false;
            foreach ($limits as $limit) {
                $html .= '<option value="'.$limit.'"';
                if ($collection->getLimit() <= $limit && !$found) {
                    $html .= ' selected="selected"';
                    $found = true;
                }
                $html .= '>'.$limit.'</option>';
            }
            $html .= '<option value="-1"';
	        if ($collection->getLimit() == -1)
	            $html .= ' selected="selected"';
	        $html .= '>-- All --</option>';
	        $html .= '<input type="hidden" name="limitstart" value="0" />';
	        $html .= '<input type="hidden" id="searchlimit" name="search" value="'.PHPFrame::Request()->get('search').'"/>';
	        $html .= '<input type="hidden" name="component" value="'.PHPFrame::Request()->getComponentName().'" />';
	        $html .= '<input type="hidden" name="action" value="'.PHPFrame::Request()->getAction().'" />';
	        $html .= '<input type="hidden" name="'.PHPFrame::Session()->getToken().'" value="1" />';
	        $html .= '</form>';
	        $html .= '</div>';
        }
        // Print subset info
        $html .= '<div class="subset_info">';
        $html .= ($collection->getTotal() > 0 ? $collection->getLimitstart()+1 : 0);
        $html .= ' - '.($collection->getLimitstart() + $collection->countRows());
        $html .= ' of '.$collection->getTotal();
        $html .= '</div>';
        
        // Print search box
        if ($search){
	        $html .= '<script language="javascript" type="text/javascript">
	                    function submit_filter(reset) {
	                        var form = document.forms["listsearchform"];
	                        
	                        if (reset){
	                            form.search.value = "";
	                        }
	                        
	                        form.submit();
	                    }
	                    
	                   </script>';
	        
	        $html .= '<form action="index.php" id="listsearchform" name="listsearchform" method="post">';
	        $html .= '<input type="text" name="search" id="search" value="'.PHPFrame::Request()->get('search').'"/>';
	        $html .= '<button type="button" class="button" onclick="submit_filter(false);">Search</button>';
	        $html .= '<button type="button" class="button" onclick="submit_filter(true);">Reset</button>';
	        $html .= '<input type="hidden" name="component" value="'.PHPFrame::Request()->getComponentName().'" />';
	        $html .= '<input type="hidden" name="action" value="'.PHPFrame::Request()->getAction().'" />';
	        $html .= '<input type="hidden" name="'.PHPFrame::Session()->getToken().'" value="1" />';
	        $html .= '</form>';
        }
        
        $html .= '</div>';
         
        return $html;
    }
    
    /**
     * Render HTML pagination for collection oject
     * 
     * @param PHPFrame_Database_RowCollection $collection The collection object for
     *                                                    which to create the pagination.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function renderPagination(
        PHPFrame_Database_RowCollection $collection, 
        $additionalparams=array()
    ) {
        $html = '';
        
        if ($collection->getPages() <= 1) {
            return $html;
        }
        
        $html .= '<div class="pagination">';
        // Print list with prev, next and so on...
        $href = 'index.php?component='.PHPFrame::Request()->getComponentName();
        $href .= '&amp;action='.PHPFrame::Request()->getAction();
        $href .= '&amp;limit='.$collection->getLimit();
        $href .= '&amp;search='.PHPFrame::Request()->get('search');
        if (PHPFrame::Request()->get('tmpl')){
        	$href .= '&amp;tmpl='.PHPFrame::Request()->get('tmpl');
        }
        if (count($additionalparams) > 0){
        	foreach ($additionalparams as $key=>$value){
	        	$href .= '&amp;'.$key.'='.$value;
        	}
        }
        
        $html .= '<ul>';
        // Start link
//        $html .= '<li class="first">';
//        if ($collection->getCurrentPage() != 1) {
//            $html .= '<a href="'.$href.'&amp;limitstart=0">Start</a>';
//        } else {
//            $html .= 'First';
//        }
//        $html .= '</li>';
        // Prev link
        $html .= '<li class="prev">';
        if ($collection->getCurrentPage() != 1) {
            $html .= '<a href="'.$href.'&amp;limitstart='.(($collection->getCurrentPage()-2) * $collection->getLimit()).'"> &lt; Prev</a>';
        } else {
            $html .= '&lt; Prev';
        }
        $html .= '</li>';
        // Page numbers
        for ($j=0; $j<$collection->getPages(); $j++) {
            $html .= '|<li class="page">';
            if ($collection->getCurrentPage() != ($j+1)) {
                $html .= '<a href="'.$href.'&amp;limitstart='.($collection->getLimit() * $j).'">'.($j+1).'</a>';    
            } else {
                $html .= ($j+1);
            }
            $html .= '</li>';
        }
        // Next link
        $html .= '|<li class="next">';
        if ($collection->getCurrentPage() != $collection->getPages()) {
            $html .= '<a href="'.$href.'&amp;limitstart='.($collection->getCurrentPage() * $collection->getLimit()).'">Next &gt; </a>';
        } else {
            $html .= 'Next &gt;';
        }
        // End link
//        $html .= '<li  class="last">';
//        if ($collection->getCurrentPage() != $collection->getPages()) {
//            $html .= '<a href="'.$href.'&amp;limitstart='.(($collection->getPages()-1) * $collection->getLimit()).'">End</a>';
//        } else {
//            $html .= 'Last';
//        }
//        $html .= '</li>';
        $html .= '</ul>';
//        $html .= '<div style="clear:both"></div>';
//        // Print page info
//        $html .= 'Page '.$collection->getCurrentPage();
//        $html .= ' of '.$collection->getPages();

        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Convert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function toString()
    {
        // Add title tag in head node 
        $head_node = $this->dom->getElementsByTagName("head")->item(0);
        $this->addNode($head_node, "title", null, $this->getTitle());
        
        // Render DOM Document as HTML string
        $html = $this->dom->saveHTML();
        
        // Add body
        $html = str_replace("{content}", $this->body, $html);
        
        return $html;
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
//            $publicId = "-//W3C//DTD HTML 4.01//EN";
//            $systemId = "http://www.w3.org/TR/html4/strict.dtd";

            $imp = new DOMImplementation;
            $this->doctype = $imp->createDocumentType($this->qualified_name, 
                                                                    null,
                                                                    null);
        }
        
        return $this->doctype;
    }
    
    /**
     * Add meta tag
     * 
     * @param string $name       This attribute identifies a property name. This 
     *                           specification does not list legal values for this 
     *                           attribute.
     * @param string $content    This attribute specifies a property's value. This 
     *                           specification does not list legal values for this 
     *                           attribute.
     * @param string $http_equiv This attribute may be used in place of the name attribute. 
     *                           HTTP servers use this attribute to gather information for 
     *                           HTTP response message headers.
     * @param string $property   This attribute may be used in place of the name attribute.
     *                           This attribute is used by facebook for open graph tags.
     * 
     * @return void
     * @since  1.0
     * @todo   This method should check whether the meta tag has already been 
     *         added to avoid printing the same meta tag twice.
     */
    function addMetaTag($name, $content, $http_equiv=null, $property=null)
    {
        // Get head node
        $head_node = $this->dom->getElementsByTagName("head")->item(0);
        
        // Creare meta tag node
        $meta_node = $this->addNode($head_node, "meta");
        
        // Add name attribute if any
        if (!is_null($name)) {
            $this->addNodeAttr($meta_node, "name", $name);
        }
        // Add http_equiv attribute if any
        if (!is_null($http_equiv)) {
            $this->addNodeAttr($meta_node, "http-equiv", $http_equiv);
        }
        //Add property attribute if any
        if (!is_null($property)) {
            $this->addNodeAttr($meta_node, "property", $property);
        }
        // Add content attribute
        $this->addNodeAttr($meta_node, "content", $content);
    }
    
    /**
     * Add linked scrip in document head
     * 
     * It takes both relative and absolute values.
     * 
     * @param string $src  The relative or absolute URL to the script source.
     * @param string $type The script type. Default is text/javascript.
     * 
     * @return void
     * @since  1.0
     * @todo   This method should check whether the script has already been 
     *         added to avoid loading the same script twice.
     */
    function addScript($src, $type='text/javascript') 
    {
        // Make source absolute URL
        $this->_makeAbsolute($src);
        
        // Get head node
        $head_node = $this->dom->getElementsByTagName("head")->item(0);
        
        // Create script tag node
        $this->addNode($head_node, "script", array("type"=>$type, "src"=>$src));
    }

    function addInlineScript($script_content, $type='text/javascript')
    {
        // Get head node
        $head_node = $this->dom->getElementsByTagName("head")->item(0);

        // Create script tag node
        $this->addNode($head_node, "script", array("type"=>$type), $script_content);
    }

    function addGoogleAnalyticsTracker($trackerid)
    {

        $script_content = "var _gaq = _gaq || [];
          _gaq.push(['_setAccount', '$trackerid']);
          _gaq.push(['_trackPageview']);

          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        ";

        $this->addInlineScript($script_content);
    }

    /**
     * Gets the first found individual element in the dom by tag name.
     * This can be used for very custom manipulation of the dom,
     * e.g. to set a custom body class
     *
     * @param string $tag_name the name of the tag e.g. 'body'
     * @return DOMNode
     */
    function getElement($tag_name) {
        $node = $this->dom->getElementsByTagName($tag_name)->item(0);

        return $node;
    }
    
    /**
     * Attach external stylesheet
     * 
     * @param string $href The relative or absolute URL to the link source.
     * @param string $type The link type. Default is text/css.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addStyleSheet($href, $type='text/css') 
    {
        // Make source absolute URL
        $this->_makeAbsolute($href);
        
        // Get head node
        $head_node = $this->dom->getElementsByTagName("head")->item(0);
        
        // Create script tag node
        $attrs = array("rel"=>"stylesheet", "href"=>$href, "type"=>$type);
        $this->addNode($head_node, "link", $attrs);
    }
    
    /**
     * Attach a link tag to the head.
     * 
     * @param $rel The rel attribute for the link tag
     * @param $href The relative or absolute URL to the link source
     * @param $type The link type.
     * 
     * @access public
     * @return void
     * @since 1.0
     */
    public function addLinkTag($rel, $href, $type)
    {
    	$this->_makeAbsolute($href);
    	
    	$head_node = $this->dom->getElementsByTagName("head")->item(0);
    	
    	$attrs = array("rel"=>$rel, "href"=>$href, "type"=>$type);
    	$this->addNode($head_node, "link", $attrs);
    }
    
    /**
     * Apply theme
     * 
     * @param PHPFrame_MVC_View $view The view object to process.
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _applyTheme(PHPFrame_MVC_View $view) 
    {
        // Make widgets available to templates
        $widgets = PHPFrame::AppRegistry()->getWidgets();
        
        if (file_exists(PHPFRAME_CONFIG_DIR.'/revision')){
            $rev = '.'.file_get_contents(PHPFRAME_CONFIG_DIR.'/revision');
            $rev = trim($rev);
        } else {
        	$rev = '';
        }
        
        // Add theme stylesheets
//        $this->addStyleSheet("themes/".PHPFrame::Config()->get("THEME")."/css/styles".$rev.".css");

        // make pathway available in local scope
        $pathway = $view->getPathway();
        
        $component_output = $this->body;
        
        // Set file name to load depending on session auth
        $session = PHPFrame::Session();
        $theme_template = $session->get('themetemplate', 'index');
        $template_filename = $theme_template.'.php';

        $template_path = PHPFRAME_INSTALL_DIR.DS."public".DS."themes".DS.PHPFrame::Config()->get("THEME");
        
        // Start buffering
        ob_start();
        require_once $template_path.DS.$template_filename;
        // save buffer in body
        $this->body = ob_get_contents();
        // clean output buffer
        ob_end_clean();

        //add google analytics tracker if set in config
        $google_analytics_id = PHPFrame::Config()->get('GOOGLE_ANALYTICS_TRACKER');
        if ($google_analytics_id && $google_analytics_id != "") {
            $this->addGoogleAnalyticsTracker($google_analytics_id);
        }

    }
    
    /**
     * Make path absolute
     * 
     * @param string $path The path we want to make absolute.
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _makeAbsolute(&$path) 
    {
        // Add the document base if a relative path
        if (substr($path, 0, 4) != 'http' && substr($path, 0, 2) != '//') {
            if (substr($path, 0, 1) == '/') {
                $path = substr($path, 1);
            }
            $uri = new PHPFrame_Utils_URI();
            $path = $uri->getBase().$path;
        }
    }
}
