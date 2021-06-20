<?php

declare(strict_types=1);

namespace Kraber\Contracts\Http\Utils;

/**
 * Interface CurlWrapperInterface
 */
interface CurlWrapperInterface
{
    /**
     * Detach the current cURL session from the wrapper.
     *
     * @return \CurlHandle|null cURL handle or null if none was initialized.
     */
    public function detach(): ?\CurlHandle;

    /**
     * Initialize a new cURL session.
     *
     * @throws \RuntimeException If an error occurred.
     */
    public function init(): void;

    /**
     * Check if cURL session is initialized.
     *
     * @return bool True if cURL session is initialized, false otherwise.
     */
    public function isOpen(): bool;

    /**
     * Close current cURL session.
     */
    public function close(): void;

    /**
     * Return the last error number for the current cURL session.
     *
     * @return int|null Last error number or null if cURL session is not open.
     */
    public function errno(): ?int;

    /**
     * Return a string containing the last error for the current cURL session.
     *
     * @return string|null The error message or null if cURL session is not open.
     */
    public function error(): ?string;

    /**
     * Return string describing the given error code.
     *
     * @param int $errno One of the cURL error codes constants.
     * @return string Returns error description.
     */
    public static function strerror(int $errno): string;

    /**
     * Get information regarding current cURL session.
     *
     * @param int|null $key
     * @return mixed If key is given, returns its value as a string. Otherwise, returns an associative array.
     */
    public function getInfo(?int $key = null): mixed;

    /**
     * Set an option for the current cURL session.
     *
     * @param int $opt
     * @param mixed $value
     * @return bool True on success or false on failure.
     */
    public function setOpt(int $opt, mixed $value): bool;

    /**
     * Set multiple options for the current cURL session.
     *
     * @param array<int, mixed> $opts
     * @return bool True if all options were successfully set. If an option could not be successfully set,
     * false is immediately returned, ignoring any future options in the options array.
     */
    public function setOptArray(array $opts): bool;

    /**
     * Gets cURL version information.
     *
     * @return array<string, mixed>|false
     */
    public static function version(): array|false;

    /**
     * Perform a cURL session.
     *
     * @return string|bool True on success or false on failure. However, if the CURLOPT_RETURNTRANSFER
     * option is set, it will return the result on success, false on failure.
     */
    public function exec(): string|bool;

    /**
     * Pause and unpause a cURL session.
     *
     * @return int Returns an error code (CURLE_OK for no error).
     */
    public function pause(int $bitmask): int;

    /**
     * Pause and unpause a cURL session.
     *
     * @return void
     */
    public function reset(): void;

    /**
     * URL encodes the given string.
     *
     * @param string $str The string to encodes.
     * @return string|false Returns escaped string or false on failure.
     */
    public function escape(string $str): string|false;

    /**
     * Decodes the given URL encoded string.
     *
     * @param string $str The string to decodes.
     * @return string|false Returns decoded string or false on failure.
     */
    public function unescape(string $str): string|false;
}
