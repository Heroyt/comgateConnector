<?php

namespace Heroyt\ComGate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class Connection implements ConnectionInterface
{

	public readonly string $version;

	/**
	 * @param string               $host     Host gateway URL
	 * @param string               $version  API version identifier - will be prepended to all paths
	 * @param string               $merchant Merchant name = e-shop identifier
	 * @param string               $secret   Secret password
	 * @param bool                 $test     Using testing or production environment
	 * @param LoggerInterface|null $logger   Optional logger object, that will log all requests made using this object
	 */
	public function __construct(
		public readonly string           $host,
		string                           $version,
		public readonly string           $merchant,
		public readonly string           $secret,
		public readonly bool             $test = false,
		public readonly ?LoggerInterface $logger = null,
	) {
		$this->version = trim(str_replace('/', '', $version));
	}

	/**
	 * Call a GET request on the API
	 *
	 * @param string $path   Path to request
	 * @param array  $params GET parameters to send
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function get(string $path, array $params = []) : ResponseInterface {
		if ($path[0] !== '/') {
			$path = '/'.$path;
		}
		$response = $this
			->getClient()
			->get(
				$this->version.$path,
				[
					'query' => $params,
				]
			);
		if (isset($this->logger)) {
			$this->logResponse($response);
		}
		return $response;
	}

	/**
	 * Get a prepared HTTP Guzzle client
	 *
	 * @return Client
	 */
	private function getClient() : Client {
		$stack = new HandlerStack();
		$stack->setHandler(new CurlHandler());
		if (isset($this->logger)) {
			$stack->push([$this, 'logRequestMiddleware']);
		}
		return new Client(
			[
				'handler'         => $stack,
				'base_uri'        => $this->host,
				'allow_redirects' => true,
				'synchronous'     => true,
				'headers'         => [
					'Accept' => 'application/x-www-form-urlencoded, application/json, text/plain'
				],
			]
		);
	}

	/**
	 * Log an API response
	 *
	 * @param ResponseInterface $response
	 *
	 * @return void
	 */
	public function logResponse(ResponseInterface $response) : void {
		if (!isset($this->logger)) {
			return;
		}

		$this->logger->log($response->getStatusCode() < 400 ? 'info' : 'error', 'Response: '.$response->getStatusCode().' '.$response->getReasonPhrase());
		$this->logger->debug('Headers: '.json_encode($response->getHeaders(), JSON_THROW_ON_ERROR));
		$this->logger->debug('Body: '.$response->getBody()->getContents());
		$response->getBody()->rewind();
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
	 * @throws GuzzleException
	 */
	public function post(string $path, array $data = []) : ResponseInterface {
		// Convert bool value to string
		foreach ($data as $key => $value) {
			if (is_bool($value)) {
				$data[$key] = $value ? 'true' : 'false';
			}
		}

		// Append mandatory auth parameters
		if (!isset($data['merchant'])) {
			$data['merchant'] = $this->merchant;
		}
		if (!isset($data['test'])) {
			$data['test'] = $this->test;
		}
		if (!isset($data['secret'])) {
			$data['secret'] = $this->secret;
		}

		if ($path[0] !== '/') {
			$path = '/'.$path;
		}

		$response = $this
			->getClient()
			->post(
				$this->version.$path,
				[
					'form_params' => $data,
				]
			);
		if (isset($this->logger)) {
			$this->logResponse($response);
		}
		return $response;
	}

	/**
	 * Log a request made to an API
	 *
	 * @param callable $handler
	 *
	 * @return callable
	 */
	public function logRequestMiddleware(callable $handler) : callable {
		return function(RequestInterface $request, array $options) use ($handler) {
			if (!isset($this->logger)) {
				return $handler($request, $options);
			}

			$this->logger->info($request->getMethod().' '.$request->getUri());
			$this->logger->debug('Headers: '.json_encode($request->getHeaders(), JSON_THROW_ON_ERROR));
			if ($this->test) {
				$this->logger->debug('Body: '.$request->getBody()->getContents());
			}
			return $handler($request, $options);
		};
	}

}