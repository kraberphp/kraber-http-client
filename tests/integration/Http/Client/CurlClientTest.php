<?php

namespace Kraber\Test\Integration\Http\Client;

use Kraber\Test\TestCase;
use Kraber\Http\Factory\ResponseFactory;
use Kraber\Http\Client\CurlClient;
use Kraber\Http\Utils\CurlWrapper;

class CurlClientTest extends TestCase
{
	public function testConstructorInitializeProperties() {
		$curlClient = new CurlClient();
		
		$this->assertInstanceOf(ResponseFactory::class, $this->getPropertyValue($curlClient, 'responseFactory'));
		$this->assertInstanceOf(CurlWrapper::class, $this->getPropertyValue($curlClient, 'cURL'));
	}
}
