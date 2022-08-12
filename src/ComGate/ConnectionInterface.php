<?php

namespace Heroyt\ComGate;

use Psr\Http\Message\ResponseInterface;

interface ConnectionInterface
{

	/**
	 * Call a GET request on the API
	 *
	 * @param string $path   Path to request
	 * @param array  $params GET parameters to send
	 *
	 * @return ResponseInterface
	 */
	public function get(string $path, array $params = []) : ResponseInterface;

	/**
	 * Call a POST request on the API
	 *
	 * Adds mandatory authentication data.
	 *
	 * @param string $path Path to request
	 * @param array  $data Form data
	 *
	 * @return ResponseInterface
	 */
	public function post(string $path, array $data = []) : ResponseInterface;
}