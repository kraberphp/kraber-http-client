<?php

namespace Kraber\Test\Integration\Http\Client;

use Http\Client\Tests\HttpClientTest;
use Psr\Http\Client\ClientInterface;
use Kraber\Http\Client\CurlClient;

class CurlHttpClientTest extends HttpClientTest
{
	protected function createHttpAdapter() : ClientInterface {
		return new CurlClient();
	}
}
