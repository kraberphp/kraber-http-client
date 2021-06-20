<?php

declare(strict_types=1);

namespace Kraber\Http\Client;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Exception;
use Throwable;

class NetworkException extends Exception implements NetworkExceptionInterface
{
    private RequestInterface $request;

    public function __construct(RequestInterface $request, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->request = $request;
    }

    /**
     * Returns the request.
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
