<?php

declare(strict_types=1);

namespace Kraber\Http\Utils;

use CurlHandle;
use Throwable;
use RuntimeException;

/**
 * Class CurlWrapper
 */
class CurlWrapper
{
	/** @var CurlHandle|null cURL handle. */
	private ?CurlHandle $handle = null;
	
	/**
	 * CurlWrapper constructor.
	 *
	 * @param CurlHandle|null $handle An existing cURL session or null.
	 */
	public function __construct(?CurlHandle $handle = null) {
		$this->handle = $handle;
	}
	
	/**
	 * Check cURL extension is enabled.
	 *
	 * @return bool True if extension is enabled, false otherwise.
	 */
	public function isCurlEnabled() : bool {
		return function_exists('curl_version');
	}
	
	/**
	 * Detach the current cURL session from the wrapper. Further calls will create a new cURL session.
	 *
	 * @return CurlHandle|null cURL handle or null if none was initialized.
	 */
	public function detach() : ?CurlHandle {
		$handle = $this->handle;
		$this->handle = null;
		
		return $handle;
	}
	
	/**
	 * Ensure cURL session is initialize otherwise try to initialize a new one.
	 *
	 * @throws RuntimeException If unable to initializes cURL session.
	 */
	private function ensureCurlSessionIsInitialized() : void {
		if ($this->isOpen() === false) {
			$this->init();
		}
	}
	
	/**
	 * Initialize a new cURL session.
	 *
	 * @throws RuntimeException If previous cURL session is not closed or if unable to initializes a new cURL session.
	 */
	public function init() : void {
		if ($this->isCurlEnabled() === false) {
			throw new RuntimeException("cURL extension is not loaded.");
		}
		
		if ($this->isOpen() === true) {
			throw new RuntimeException("Previous cURL session has not been closed / detached.");
		}
		
		$this->handle = curl_init();
	}
	
	/**
	 * Check if cURL session is initialized.
	 *
	 * @return bool True if cURL session is initialized, false otherwise.
	 */
	public function isOpen() : bool {
		return $this->handle !== null;
	}
	
	/**
	 * Close current cURL session.
	 */
	public function close() : void {
		if ($this->isOpen() === true) {
			curl_close($this->handle);
			$this->handle = null;
		}
	}
	
	/**
	 * Return the last error number for the current cURL session.
	 *
	 * @return int|null Last error number or null if cURL session is not open.
	 */
	public function errno(): ?int {
		return $this->isOpen() === true ?
			curl_errno($this->handle):
			null;
	}
	
	/**
	 * Return a string containing the last error for the current cURL session.
	 *
	 * @return string|null The error message (or an empty string if no error occurred) or null if cURL session is not open.
	 */
	public function error(): ?string {
		return $this->isOpen() === true ?
			curl_error($this->handle):
			null;
	}
	
	/**
	 * Return string describing the given error code.
	 *
	 * @param int $errno One of the cURL error codes constants.
	 * @return string Returns error description.
	 */
	public static function strerror(int $errno) : string {
		return curl_strerror($errno);
	}
	
	/**
	 * Get information regarding current cURL session.
	 *
	 * @param int|null $key
	 * @return mixed If key is given, returns its value as a string. Otherwise, returns an associative array.
	 * 		If cURL session is not active, null will be returned.
	 */
	public function getInfo(?int $key = null) : mixed {
		return $this->isOpen() === true ?
			curl_getinfo($this->handle, $key) :
			null;
	}
	
	/**
	 * Set an option for the current cURL transfert.
	 * This method will try to initialize a cURL session if none was available.
	 *
	 * @param int $opt
	 * @param mixed $value
	 * @return bool True on success or false on failure.
	 * @throws RuntimeException If unable to create a new cURL session.
	 */
	public function setOpt(int $opt, mixed $value) : bool {
		$this->ensureCurlSessionIsInitialized();
		
		return curl_setopt($this->handle, $opt, $value);
	}
	
	/**
	 * Set multiple options for the current cURL transfert.
	 * This method will try to initialize a cURL session if none was available.
	 *
	 * @param array $opts
	 * @return bool True if all options were successfully set. If an option could not be successfully set,
	 * false is immediately returned, ignoring any future options in the options array.
	 * @throws RuntimeException If unable to create a new cURL session.
	 */
	public function setOptArray(array $opts) : bool {
		$this->ensureCurlSessionIsInitialized();
		
		return curl_setopt_array($this->handle, $opts);
	}
	
	/**
	 * Gets cURL version information.
	 *
	 * @return array|false
	 */
	public static function version() : array|false {
		return curl_version();
	}
	
	/**
	 * Perform a cURL session.
	 *
	 * @return string|bool True on success or false on failure. However, if the CURLOPT_RETURNTRANSFER
	 * option is set, it will return the result on success, false on failure.
	 * @throws RuntimeException If no cURL session is initialized.
	 */
	public function exec() : string|bool {
		if ($this->isOpen() === false) {
			throw new RuntimeException("cURL session is not initialized.");
		}
		
		return curl_exec($this->handle);
	}
	
	/**
	 * Pause and unpause a cURL session.
	 *
	 * @return int Returns an error code (CURLE_OK for no error).
	 * @throws RuntimeException If no cURL session is initialized.
	 */
	public function pause(int $bitmask) : int {
		if ($this->isOpen() === false) {
			throw new RuntimeException("cURL session is not initialized.");
		}
		
		return curl_pause($this->handle, $bitmask);
	}
	
	/**
	 * Pause and unpause a cURL session.
	 * This method will try to initialize a cURL session if none was available.
	 *
	 * @return int Returns an error code (CURLE_OK for no error).
	 * @throws RuntimeException If unable to create a new cURL session.
	 */
	public function reset() : void {
		$this->ensureCurlSessionIsInitialized();
		
		curl_reset($this->handle);
	}
	
	/**
	 * URL encodes the given string.
	 * This method will try to initialize a cURL session if none was available.
	 *
	 * @param string $str The string to encodes.
	 * @return string|false Returns escaped string or false on failure.
	 * @throws RuntimeException If unable to create a new cURL session.
	 */
	public function escape(string $str) : string|false {
		$this->ensureCurlSessionIsInitialized();
		
		return curl_escape($this->handle, $str);
	}
	
	/**
	 * Decodes the given URL encoded string.
	 * This method will try to initialize a cURL session if none was available.
	 *
	 * @param string $str The string to decodes.
	 * @return string|false Returns decoded string or false on failure.
	 * @throws RuntimeException If unable to create a new cURL session.
	 */
	public function unescape(string $str) : string|false {
		$this->ensureCurlSessionIsInitialized();
		
		return curl_unescape($this->handle, $str);
	}
}
