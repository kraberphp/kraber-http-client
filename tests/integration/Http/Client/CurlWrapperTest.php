<?php

declare(strict_types=1);

namespace Kraber\Test\Integration\Http\Client;

use Kraber\Test\TestCase;
use Kraber\Http\Utils\CurlWrapper;
use CurlHandle;
use RuntimeException;
use Mockery;

class CurlWrapperTest extends TestCase
{
    protected function mockeryTestTearDown()
    {
        parent::mockeryTestTearDown();
        Mockery::close();
    }

    public function testConstructorInitializeProperties()
    {
        $curl = new CurlWrapper();

        $this->assertNull($this->getPropertyValue($curl, 'handle'));
    }

    public function testConstructorInitializePropertiesWithExistingCurlHandle()
    {
        $handle = curl_init();
        $curl = new CurlWrapper($handle);

        $this->assertInstanceOf(CurlHandle::class, $this->getPropertyValue($curl, 'handle'));
        $this->assertSame($handle, $this->getPropertyValue($curl, 'handle'));
    }

    public function testDetach()
    {
        $handle = curl_init();
        $curl = new CurlWrapper($handle);

        $usedHandle = $curl->detach();
        $this->assertNull($this->getPropertyValue($curl, 'handle'));
        $this->assertSame($handle, $usedHandle);

        $usedHandle = $curl->detach();
        $this->assertNull($this->getPropertyValue($curl, 'handle'));
        $this->assertNull($usedHandle);
    }

    public function testInit()
    {
        $curl = new CurlWrapper();
        $curl->init();

        $this->assertInstanceOf(CurlHandle::class, $this->getPropertyValue($curl, 'handle'));
    }

    public function testInitThrowsExceptionIfCurlExtensionIsDisabled()
    {
        $curl = Mockery::mock(CurlWrapper::class);
        $curl->makePartial()->shouldReceive('isCurlEnabled')->andReturn(false);

        $this->expectException(RuntimeException::class);
        $curl->init();
    }

    public function testInitThrowsExceptionIfPreviousCurlSessionIsOpen()
    {
        $curl = new CurlWrapper();
        $curl->init();

        $this->expectException(RuntimeException::class);
        $curl->init();
    }

    public function testClose()
    {
        $curl = new CurlWrapper();
        $curl->init();
        $this->assertInstanceOf(CurlHandle::class, $this->getPropertyValue($curl, 'handle'));

        $curl->close();
        $this->assertNull($this->getPropertyValue($curl, 'handle'));
    }

    public function testErrnoReturnZeroWhenNoErrorOccurred()
    {
        $curl = new CurlWrapper();
        $curl->init();
        $this->assertEquals(0, $curl->errno());
    }

    public function testErrnoReturnNullWhenCurlSessionIsNotOpen()
    {
        $curl = new CurlWrapper();
        $this->assertNull($curl->errno());
    }

    public function testErrorReturnEmptyStringWhenNoErrorOccurred()
    {
        $curl = new CurlWrapper();
        $curl->init();
        $this->assertEquals("", $curl->error());
    }

    public function testErrorReturnNullWhenCurlSessionIsNotOpen()
    {
        $curl = new CurlWrapper();
        $this->assertNull($curl->error());
    }

    public function testStrerrorReturnNullWhenArgumentIsInvalid()
    {
        $curl = new CurlWrapper();
        $this->assertIsString($curl->strerror(-1));
        $this->assertEquals("Unknown error", $curl->strerror(-1));
    }

    public function testGetInfo()
    {
        $curl = new CurlWrapper();
        $curl->init();

        $this->assertIsArray($curl->getInfo());
    }

    public function testGetInfoReturnNullWhenCurlSessionIsNotOpen()
    {
        $curl = new CurlWrapper();
        $this->assertNull($curl->getInfo());
    }

    public function testSetOpt()
    {
        $curl = new CurlWrapper();
        $curl->init();

        $this->assertTrue($curl->setOpt(CURLOPT_URL, "https://httpbin.org/get"));
    }

    public function testSetOptInitializeCurlSessionIfNotOpen()
    {
        $curl = new CurlWrapper();

        $this->assertNull($this->getPropertyValue($curl, 'handle'));
        $this->assertTrue($curl->setOpt(CURLOPT_URL, "https://httpbin.org/get"));
        $this->assertInstanceOf(CurlHandle::class, $this->getPropertyValue($curl, 'handle'));
    }

    public function testSetOptArray()
    {
        $curl = new CurlWrapper();
        $curl->init();

        $this->assertTrue($curl->setOptArray([CURLOPT_URL => "https://httpbin.org/get"]));
    }

    public function testSetOptArrayInitializeCurlSessionIfNotOpen()
    {
        $curl = new CurlWrapper();

        $this->assertNull($this->getPropertyValue($curl, 'handle'));
        $this->assertTrue($curl->setOptArray([CURLOPT_URL => "https://httpbin.org/get"]));
        $this->assertInstanceOf(CurlHandle::class, $this->getPropertyValue($curl, 'handle'));
    }

    public function testVersion()
    {
        $curl = new CurlWrapper();
        $this->assertIsArray($curl->version());
    }

    public function testExec()
    {
        $curl = new CurlWrapper();
        $curl->init();
        $curl->setOpt(CURLOPT_URL, "https://httpbin.org/get");
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);

        $this->assertIsNotBool($curl->exec());
    }

    public function testExecThrowsExceptionIfCurlSessionIsNotOpen()
    {
        $curl = new CurlWrapper();
        $this->expectException(RuntimeException::class);
        $curl->exec();
    }

    public function testPause()
    {
        $curl = new CurlWrapper();
        $curl->init();
        $this->assertEquals(CURLE_OK, $curl->pause(CURLPAUSE_ALL));
    }

    public function testPauseThrowsExceptionIfCurlSessionIsNotOpen()
    {
        $curl = new CurlWrapper();
        $this->expectException(RuntimeException::class);
        $curl->pause(CURLPAUSE_ALL);
    }

    public function testResetInitializeCurlSessionIfNotOpen()
    {
        $curl = new CurlWrapper();
        $this->assertNull($this->getPropertyValue($curl, 'handle'));
        $curl->reset();
        $this->assertInstanceOf(CurlHandle::class, $this->getPropertyValue($curl, 'handle'));
    }

    public function testEscape()
    {
        $curl = new CurlWrapper();

        $this->assertSame("foo%20bar", $curl->escape("foo bar"));
    }

    public function testUnescape()
    {
        $curl = new CurlWrapper();

        $this->assertSame("foo bar", $curl->unescape("foo%20bar"));
    }
}
