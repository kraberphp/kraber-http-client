<?php

declare(strict_types=1);

namespace Kraber\Http\Client;

use Psr\Http\Client\ClientExceptionInterface;
use Exception;
use Throwable;

class ClientException extends Exception implements ClientExceptionInterface
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
