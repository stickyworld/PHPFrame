<?php
// Include framework if not inculded yet
require_once preg_replace("/data\/.*/", "src/PHPFrame.php", __FILE__);

class ConfigControllerTest extends PHPUnit_Framework_TestCase
{
    private $_app;
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        PHPFrame::dataDir(preg_replace("/CLI_Tool\/.*/", "", __FILE__));
        
        $install_dir = preg_replace("/tests\/.*/", "", __FILE__);
        
        $this->_app = new PHPFrame_Application(array(
            "install_dir" => $install_dir
        ));
    }
    
    public function tearDown()
    {
        //...
        PHPFrame::getSession()->getSysevents()->clear();
    }
    
    public function test_show()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("config");
        $request->action("show");
        $request->param("install_dir", $this->_app->getInstallDir());
        
        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();
        
        $this->assertRegExp(
            "/app_name = PHPFrame Command Line Tool/", 
            (string) $this->_app->response()
        );
    }
    
    public function test_get()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("config");
        $request->action("get");
        $request->param("key", "secret");
        $request->param("install_dir", $this->_app->getInstallDir());
        
        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();
        
        $this->assertRegExp(
            "/secret: ChangeMeToSomethingRandomAndComplicated/", 
            (string) $this->_app->response()
        );
    }
    
    public function test_getFailureUnknownKey()
    {
        $this->setExpectedException("LogicException");
        
        $request = new PHPFrame_Request();
        $request->controllerName("config");
        $request->action("get");
        $request->param("key", "secretttt");
        $request->param("install_dir", $this->_app->getInstallDir());
        
        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();
    }
    
    public function test_set()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("config");
        $request->action("set");
        $request->param("key", "secret");
        $request->param("value", "abc");
        $request->param("install_dir", $this->_app->getInstallDir());
        
        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();
        
        $this->assertRegExp(
            "/secret: abc/", 
            (string) $this->_app->response()
        );
        
        $request = new PHPFrame_Request();
        $request->controllerName("config");
        $request->action("set");
        $request->param("key", "secret");
        $request->param("value", "ChangeMeToSomethingRandomAndComplicated");
        $request->param("install_dir", $this->_app->getInstallDir());
        
        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();
        
        $this->assertRegExp(
            "/secret: ChangeMeToSomethingRandomAndComplicated/", 
            (string) $this->_app->response()
        );
    }
}