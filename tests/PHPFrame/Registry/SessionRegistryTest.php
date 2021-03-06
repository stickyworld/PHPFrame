<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_SessionRegistryTest extends PHPUnit_Framework_TestCase
{
    private $_session;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $this->_session = PHPFrame::getSession();
    }

    public function tearDown()
    {
        //...
    }

    public function test_getIterator()
    {
        $array = iterator_to_array($this->_session);
        $this->assertType("array", $array);
        $this->assertArrayHasKey("id", $array);
        $this->assertArrayHasKey("user", $array);
    }

    public function test_get()
    {
        $this->assertNull($this->_session->get("nonexistant_var"));

        $this->assertEquals(
            "some_value",
            $this->_session->get("some_var", "some_value")
        );
    }

    public function test_set()
    {
        $this->_session->set("some_var", "some_value");
        $this->assertEquals("some_value", $this->_session->get("some_var"));
    }

    public function test_getId()
    {
        $this->assertEquals("", $this->_session->getId());
    }

    public function test_getName()
    {
        $this->assertEquals("PHPFrame", $this->_session->getName());
    }

    public function test_getClient()
    {
        $this->assertType("PHPFrame_Client", $this->_session->getClient());
    }

    public function test_getUser()
    {
        $this->assertType("PHPFrame_User", $this->_session->getUser());
    }

    public function test_setUser()
    {
        //...
    }

    public function test_isAuth()
    {
        $this->assertTrue($this->_session->isAuth());

        $user = $this->_session->getUser();
        $this->_session->setUser(new PHPFrame_User);

        $this->assertFalse($this->_session->isAuth());

        $this->_session->setUser($user);
    }

    public function test_isAdmin()
    {
        $this->assertTrue($this->_session->isAdmin());

        $user = $this->_session->getUser();
        $this->_session->setUser(new PHPFrame_User);

        $this->assertFalse($this->_session->isAdmin());

        $this->_session->setUser($user);
    }

    public function test_getSysevents()
    {
        $this->assertType("PHPFrame_Sysevents", $this->_session->getSysevents());
    }

    public function test_getToken()
    {
        $token = $this->_session->getToken();
        $this->assertType("string", $token);
        $this->assertRegExp("/^[a-z-A-Z0-9]{32}$/", $token);
        $this->assertEquals($token, $this->_session->getToken());

        $this->assertNotEquals($token, $this->_session->getToken(true));
    }

    public function test_destroy()
    {
        $this->assertNull($this->_session->destroy());
    }
}
