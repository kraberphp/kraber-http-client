<?php

declare(strict_types=1);

namespace Kraber\Test\Integration\Http\Client;

use Http\Client\Tests\HttpFeatureTest;
use Psr\Http\Client\ClientInterface;
use Kraber\Http\Client\CurlClient;

class CurlHttpFeatureTest extends HttpFeatureTest
{
    protected function createClient(): ClientInterface
    {
        return new CurlClient();
    }
}
