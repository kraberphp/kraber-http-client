<?php

declare(strict_types=1);

namespace Kraber\Http\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Kraber\Http\Factory\ResponseFactory;
use CurlHandle;
use Throwable;

class CurlClient implements ClientInterface
{
	private ResponseFactoryInterface $responseFactory;
	private ?CurlHandle $handle = null;
	
	/**
	 * CurlClient constructor.
	 *
	 * @param ResponseFactoryInterface|null $responseFactory Factory used to produce ResponseInterface.
	 * @throws CurlClientException If cURL is not loaded.
	 */
	public function __construct(?ResponseFactoryInterface $responseFactory = null) {
		if (!function_exists('curl_version')) {
			throw new CurlClientException("cURL extension is not loaded.");
		}
		
		if ($responseFactory === null) {
			$responseFactory = new ResponseFactory();
		}
		
		$this->responseFactory = $responseFactory;
	}
	
	/**
	 * Sends a PSR-7 request and returns a PSR-7 response.
	 *
	 * @param RequestInterface $request
	 * @return ResponseInterface
	 * @throws ClientExceptionInterface If an error happens while processing the request.
	 */
	public function sendRequest(RequestInterface $request) : ResponseInterface {
		$this->ensureCurlResourceHandleIsValid();
		
		$response = $this->responseFactory->createResponse();
		
		curl_reset($this->handle);
		curl_setopt_array($this->handle, $this->generateCurlOptionsFromRequest($request));
		curl_setopt($this->handle, CURLOPT_HEADERFUNCTION, function($handle, $header) use (&$response) {
			$length = strlen($header);
			$headerLine = array_map('trim', explode(':', $header, 2));
			if (!isset($headerLine[0]) || !isset($headerLine[1])) {
				return $length;
			}
			
			$response = $response->withAddedHeader($headerLine[0], $headerLine[1]);
			
			return $length;
		});
		$result = curl_exec($this->handle);
		
		if ($result === false) {
			throw new NetworkException($request, curl_strerror(curl_errno($this->handle)));
		}
		
		try {
			$headerSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);
			$responseBody = substr($result, $headerSize);
			
			$response = $response->withStatus(curl_getinfo($this->handle, CURLINFO_HTTP_CODE));
			$response->getBody()->write($responseBody);
		}
		catch (Throwable $t) {
			throw new CurlClientException($request, $t->getMessage());
		}
		
		return $response;
	}
	
	private function ensureCurlResourceHandleIsValid() : void {
		if ($this->handle === null) {
			try {
				$this->handle = curl_init();
			}
			catch (Throwable) {
				$this->handle = null;
				throw new CurlClientException("Unable to initializes cURL handle.");
			}
		}
	}
	
	private function generateCurlOptionsFromRequest(RequestInterface $request) : array {
		$options = [
			CURLOPT_CUSTOMREQUEST => $request->getMethod(),
			CURLOPT_URL => $request->getUri()->__toString(),
			CURLOPT_ENCODING => "",
			CURLOPT_HEADER => true,
			CURLOPT_RETURNTRANSFER => true,
		];
		
		$content = $request->getBody()->getContents();
		if ($content !== "") {
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = $content;
			
			if (!$request->hasHeader('Content-Length')) {
				$request = $request->withHeader('Content-Length', (string) strlen($content));
			}
			
			if (!$request->hasHeader('Content-Type')) {
				$request = $request->withHeader('Content-Type', 'text/plain');
			}
		}
		
		$options[CURLOPT_HTTPHEADER] = [];
		foreach ($request->getHeaders() as $headerName => $headerValues) {
			$options[CURLOPT_HTTPHEADER][] = trim($headerName).": ".implode(",", $headerValues);
		}
		
		return $options;
	}
}
