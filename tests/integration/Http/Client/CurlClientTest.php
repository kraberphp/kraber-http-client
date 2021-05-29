<?php

namespace Kraber\Test\Integration\Http\Client;

use Kraber\Test\TestCase;
use Kraber\Http\Factory\{
	ResponseFactory,
	RequestFactory
};
use Kraber\Http\Client\CurlClient;
use Kraber\Http\Utils\CurlWrapper;
use Psr\Http\Client\{
	ClientExceptionInterface,
	NetworkExceptionInterface
};
use Mockery;

class CurlClientTest extends TestCase
{
	private $cURL = null;
	protected function mockeryTestSetUp() {
		parent::mockeryTestSetUp();
		$this->cURL = Mockery::mock(CurlWrapper::class);
	}
	
	protected function mockeryTestTearDown() {
		parent::mockeryTestTearDown();
		Mockery::close();
	}
	
	public function testConstructorInitializeProperties() {
		$curlClient = new CurlClient();
		
		$this->assertInstanceOf(ResponseFactory::class, $this->getPropertyValue($curlClient, 'responseFactory'));
		$this->assertInstanceOf(CurlWrapper::class, $this->getPropertyValue($curlClient, 'cURL'));
	}
	
	public function testSendRequestThrowsExceptionIfCurlExtensionIsDisabled() {
		$this->cURL = Mockery::mock(CurlWrapper::class);
		$this->cURL->makePartial()
			->shouldReceive('isCurlEnabled')
			->andReturn(false);
		
		$requestFactory = new RequestFactory();
		$this->expectException(ClientExceptionInterface::class);
		$curlClient = new CurlClient(new ResponseFactory(), $this->cURL);
		$curlClient->sendRequest($requestFactory->createRequest("GET", "https://httpbin.org/get"));
	}
	
	public function testSendRequestThrowsExceptionOnInvalidHttpStatusCode() {
		$this->cURL = Mockery::mock(CurlWrapper::class);
		$this->cURL->makePartial()
			->shouldReceive('getInfo')
			->with(CURLINFO_HTTP_CODE)
			->andReturn(42);
		
		$requestFactory = new RequestFactory();
		$this->expectException(ClientExceptionInterface::class);
		$curlClient = new CurlClient(new ResponseFactory(), $this->cURL);
		$curlClient->sendRequest($requestFactory->createRequest("GET", "https://httpbin.org/get"));
	}
	
	public function testSendRequestThrowsExceptionOnCurlExecFailure() {
		$this->cURL = Mockery::mock(CurlWrapper::class);
		$this->cURL->makePartial()
			->shouldReceive('exec')
			->andReturn(false);
		
		$request = (new RequestFactory())->createRequest("GET", "https://httpbin.org/get");
		
		$this->expectException(NetworkExceptionInterface::class);
		try {
			$curlClient = new CurlClient(new ResponseFactory(), $this->cURL);
			$curlClient->sendRequest($request);
		}
		catch (NetworkExceptionInterface $e) {
			$this->assertSame($request, $e->getRequest());
			throw $e;
		}
	}
}
