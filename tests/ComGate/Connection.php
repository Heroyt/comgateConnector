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

	public string $switch = '';

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
		$label = $data['label'] ?? $this->switch;
		return match ($path) {
			'/status', 'status', 'status/' => new Response(
				200,
				[
					'Content-Type' => $label === 'invalidData' ? 'text/plain' : 'application/x-www-form-urlencoded; charset=utf-8',
				],
				$this->getStatusResponse($data['transId'])
			),
			'/create', 'create', 'create/' => new Response(
				200,
				[
					'Content-Type' => $label === 'invalidData' ? 'text/plain' : 'application/x-www-form-urlencoded; charset=utf-8',
				],
				$this->getCreateResponse($label)
			),
			'/capturePreauth', 'capturePreauth', 'capturePreauth/', '/cancelPreauth', 'cancelPreauth', 'cancelPreauth/' => new Response(
				200,
				[
					'Content-Type' => $label === 'invalidData' ? 'text/plain' : 'application/x-www-form-urlencoded; charset=utf-8',
				],
				$this->getCapturePreauthResponse($label)
			),
			default => new Response(404, body: 'Page not found'),
		};
	}

	private function getCreateResponse(string $label) : string {
		return match ($label) {
			'invalid1' => 'code=1200&message=DBerror',
			'invalid2' => 'code=0&message=OK',
			'invalid3' => 'code=0&message=OK&transId=AB12-EF34-IJ56',
			'invalid4' => 'code=0&message=OK&transId=AB12-EF34-IJ56&redirect=invalidUrl',
			default => 'code=0&message=OK&transId=AB12-EF34-IJ56&redirect=https%3A%2F%2Fpayments.comgate.cz%2Fclient%2Finstructions%2Findex%3Fid%3DABCDEFGHI'
		};
	}

	private function getCapturePreauthResponse(string $label) : string {
		return match ($label) {
			'invalid' => 'code=1200&message=DBerror',
			default => 'code=0&message=OK',
		};
	}

	private function getStatusResponse(string $transId) : string {
		return match ($transId) {
			'AB12' => 'code=0&message=OK&status=PENDING&price=100&curr=CZK&label=test&refId=123&email=test@test.cz',
			'1234' => 'code=0&message=OK&status=AUTHORIZED&price=100&curr=CZK&label=test&refId=123&email=test@test.cz&payerId=999&method=ALL',
			'ABCD' => 'code=0&message=OK&status=CANCELLED&price=80&curr=CZK&label=test&refId=123&email=test@test.cz&payerId=999&method=ALL&account=abcdefg&phone=123456789&name=test&payerName=TestTestovi??&payerAcc=888888&fee=20&eetData='.urlencode(json_encode(['key' => 'value'], JSON_THROW_ON_ERROR)),
			'invalid' => 'code=1200&message=DBerror',
		};
	}
}