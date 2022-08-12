<?php

namespace Testing\ComGate;

use GuzzleHttp\Psr7\Response;
use Heroyt\ComGate\ConnectionInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Dummy connection class to use for testing
 */
class Connection implements ConnectionInterface
{

	/**
	 * Call a GET request on the API
	 *
	 * @param string $path   Path to request
	 * @param array  $params GET parameters to send
	 *
	 * @return ResponseInterface
	 */
	public function get(string $path, array $params = []) : ResponseInterface {
		return match ($path) {
			default => new Response(404, body: 'Page not found'),
		};
	}

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
	public function post(string $path, array $data = []) : ResponseInterface {
		return match ($path) {
			'/create', 'create', 'create/' => new Response(
				200,
				[
					'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
				],
				'code=0&message=OK&transId=AB12-EF34-IJ56&redirect=https%3A%2F%2Fpayments.comgate.cz%2Fclient%2Finstructions%2Findex%3Fid%3DABCDEFGHI'
			),
			default => new Response(404, body: 'Page not found'),
		};
	}
}