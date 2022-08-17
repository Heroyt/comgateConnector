<?php

namespace Testing\ComGate\API;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Heroyt\ComGate\Connection;
use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Payment\Currency;
use Heroyt\ComGate\Payment\Payment;
use Heroyt\ComGate\Payment\State;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Testing\ComGate\Logger;

class ConnectionTest extends TestCase
{

	private Container $container;

	public function __construct(?string $name = null, array $data = [], $dataName = '') {
		parent::__construct($name, $data, $dataName);

		// Set up a DI container
		$loader = new ContainerLoader(__DIR__.'/../../../temp');
		$class = $loader->load(function($compiler) {
			$compiler->loadConfig(__DIR__.'/../../services.neon');
		});
		$this->container = new $class;
	}

	public function testGet() : void {
		$params = $this->container->getParameters();
		$connection = new Connection($params['comgate']['host'], 'merchant', $params['comgate']['merchant'], $params['comgate']['secret']);

		$response = $connection->get('ip');
		self::assertSame(200, $response->getStatusCode());
		$body = $response->getBody()->getContents();
		self::assertNotEmpty($body);
	}

	public function testPost() : void {
		$params = $this->container->getParameters();
		$connection = new Connection($params['comgate']['host'], 'merchant', $params['comgate']['merchant'], $params['comgate']['secret']);

		$response = $connection->post('ip');
		self::assertSame(200, $response->getStatusCode());
		$body = $response->getBody()->getContents();
		self::assertNotEmpty($body);
	}

	public function testLogging() : void {
		$params = $this->container->getParameters();
		$connection = new Connection(
			$params['comgate']['host'],
			'merchant',
			$params['comgate']['merchant'],
			$params['comgate']['secret'],
			true,
			new Logger()
		);

		// Test response
		$response = $connection->get('ip');
		self::assertSame(200, $response->getStatusCode());
		$body = $response->getBody()->getContents();
		$response->getBody()->rewind();
		self::assertNotEmpty($body);

		// Test logging
		self::assertEquals(
			[
				'level'   => 'info',
				'message' => 'GET '.$params['comgate']['host'].'merchant/ip',
				'context' => [],
			],
			Logger::$lines[0]
		);
		self::assertEquals(
			[
				'level'   => 'debug',
				'message' => 'Headers: '.json_encode(
																																																																																																																																																																																																																																																																																				[
																																																																																																																																																																																																																																																																																			 'Accept'     => ['application/x-www-form-urlencoded, application/json, text/plain'],
																																																																																																																																																																																																																																																																																			 'User-Agent' => ['GuzzleHttp/7'],
																																																																																																																																																																																																																																																																																			 'Host'       => [parse_url($params['comgate']['host'], PHP_URL_HOST)],
																																																																																																																																																																																																																																																																																		 ], JSON_THROW_ON_ERROR),
				'context' => [],
			],
			Logger::$lines[1]
		);
		self::assertEquals(
			[
				'level'   => 'debug',
				'message' => 'Body: ',
				'context' => [],
			],
			Logger::$lines[2]
		);
		self::assertEquals(
			[
				'level'   => 'info',
				'message' => 'Response: '.$response->getStatusCode().' '.$response->getReasonPhrase(),
				'context' => [],
			],
			Logger::$lines[3]
		);
		self::assertEquals(
			[
				'level'   => 'debug',
				'message' => 'Headers: '.json_encode($response->getHeaders(), JSON_THROW_ON_ERROR),
				'context' => [],
			],
			Logger::$lines[4]
		);
		self::assertEquals(
			[
				'level'   => 'debug',
				'message' => 'Body: '.$body,
				'context' => [],
			],
			Logger::$lines[5]
		);
	}

	public function testNoLogger() : void {
		$params = $this->container->getParameters();
		$connection = new Connection(
			$params['comgate']['host'],
			'merchant',
			$params['comgate']['merchant'],
			$params['comgate']['secret'],
		);

		Logger::$lines = [];
		$connection->logResponse(new Response());
		self::assertEmpty(Logger::$lines);

		$handler = static function(RequestInterface $request, array $options) : int {
			return 3314;
		};

		self::assertEquals(3314, $connection->logRequestMiddleware($handler)(new Request('GET', 'test'), []));
	}

	public function getFieldsCreate() : array {
		return [
			[
				[
					'price'    => 100,
					'currency' => Currency::CZK,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				]
			],
		];
	}

	/**
	 * @dataProvider getFieldsCreate
	 *
	 * @param array $fields
	 *
	 * @return void
	 */
	public function testPayment(array $fields) : void {
		/** @var Payment $payment */
		$payment = $this->container->getService('comgate.payment');

		foreach ($fields as $key => $value) {
			$payment->$key = $value;
		}

		// Test creation
		try {
			$redirect = $payment->create();
		} catch (ApiException $e) {
			foreach (Logger::$lines as ['level' => $level, 'message' => $message]) {
				echo strtoupper($level).': '.$message.PHP_EOL;
			}
			throw $e;
		}
		self::assertNotEmpty($payment->transId);
		self::assertNotEmpty($redirect);

		// Test getting of status
		/** @var Payment $paymentInfo */
		$paymentInfo = $this->container->getService('comgate.payment');
		$paymentInfo->transId = $payment->transId;

		$paymentInfo->getStatus();

		// Test if fields parsed from the API are the same
		self::assertEquals($payment->label, $paymentInfo->label);
		self::assertEquals($payment->refId, $paymentInfo->refId);
		self::assertEquals($payment->email, $paymentInfo->email);
		self::assertEquals($payment->currency, $paymentInfo->currency);
		self::assertEquals(State::PENDING, $paymentInfo->state);
	}

}
