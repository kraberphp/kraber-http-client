<?php

declare(strict_types=1);

namespace Kraber\Http\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{
	ResponseFactoryInterface,
	RequestInterface,
	ResponseInterface
};
use Kraber\Http\Factory\ResponseFactory;
use Kraber\Http\Utils\CurlWrapper;
use CurlHandle;
use Throwable;
use RuntimeException;

/**
 * Class CurlClient
 */
class CurlClient implements ClientInterface
{
	/** @var ResponseFactoryInterface The response factory used to generate ResponseInterface. */
	private ResponseFactoryInterface $responseFactory;
	
	/** @var CurlWrapper cURL function wrapper. */
	private CurlWrapper $cURL;
	
	/**
	 * CurlClient constructor.
	 *
	 * @param ResponseFactoryInterface|null $responseFactory Factory used to produce ResponseInterface.
	 * If null is given Kraber\Http\Factory\ResponseFactory will be used.
	 * @param CurlHandle|CurlWrapper|null $handle cURL handle to use, CurlWrapper or null.
	 * @throws ClientException If cURL extension is not loaded.
	 */
	public function __construct(?ResponseFactoryInterface $responseFactory = null, CurlHandle|CurlWrapper|null $handle = null) {
		if ($responseFactory === null) {
			$responseFactory = new ResponseFactory();
		}
		$this->responseFactory = $responseFactory;
		
		try {
			$this->cURL = new CurlWrapper($handle);
		}
		catch (RuntimeException $e) {
			throw new ClientException($e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * Sends a PSR-7 request and returns a PSR-7 response.
	 *
	 * @param RequestInterface $request
	 * @return ResponseInterface
	 * @throws ClientException If an error happens while processing the request.
	 * @throws NetworkException If an error happens during cURL exec.
	 */
	public function sendRequest(RequestInterface $request) : ResponseInterface {
		$response = $this->responseFactory->createResponse();
		
		$this->ensureCurlSessionIsInitialized();
		$this->cURL->reset();
		$this->cURL->setOptArray($this->createCurlOptionsArrayFromRequest($request));
		$this->cURL->setOpt(CURLOPT_HEADERFUNCTION, function($handle, $header) use (&$response) {
			$length = strlen($header);
			$headerLine = array_map('trim', explode(':', $header, 2));
			if (!isset($headerLine[0]) || !isset($headerLine[1])) {
				return $length;
			}
			
			$response = $response->withAddedHeader($headerLine[0], $headerLine[1]);
			return $length;
		});
		
		$result = $this->cURL->exec();
		if ($result === false) {
			throw new NetworkException($request, $this->cURL->error());
		}
		
		try {
			$responseBody = substr($result, $this->cURL->getInfo(CURLINFO_HEADER_SIZE));
			$response = $response->withStatus($this->cURL->getInfo(CURLINFO_HTTP_CODE));
			$response->getBody()->write($responseBody);
		}
		catch (Throwable $t) {
			throw new ClientException($request, $t->getMessage());
		}
		
		return $response;
	}
	
	/**
	 * Ensure cURL session is initialized.
	 *
	 * @throws ClientException If unable to initializes cURL session.
	 */
	private function ensureCurlSessionIsInitialized() : void {
		if ($this->cURL->isOpen() === false) {
			try {
				$this->cURL->init();
			}
			catch (Throwable) {
				throw new ClientException("Unable to initializes cURL session.");
			}
		}
	}
	
	/**
	 * Create an array with cURL options based on provided request.
	 *
	 * @param RequestInterface $request The request to send.
	 * @return array cURL options for the transfert.
	 */
	private function createCurlOptionsArrayFromRequest(RequestInterface $request) : array {
		$options = [
			CURLOPT_CUSTOMREQUEST => $request->getMethod(),
			CURLOPT_URL => $request->getUri()->__toString(),
			CURLOPT_ENCODING => "",
			CURLOPT_RETURNTRANSFER => true
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
			$options[CURLOPT_HTTPHEADER][] = trim($headerName).": ".$request->getHeaderLine($headerName);
		}
		
		return $options;
	}
}
