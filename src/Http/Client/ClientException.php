<?php

declare(strict_types=1);

namespace Kraber\Http\Client;

use Psr\Http\Client\ClientExceptionInterface;
use Exception;

class ClientException extends Exception implements ClientExceptionInterface
{

}
