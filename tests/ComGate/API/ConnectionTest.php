<?php

namespace Testing\ComGate\API;

use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Payment\Currency;
use Heroyt\ComGate\Payment\Lang;
use Heroyt\ComGate\Payment\Payment;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use PHPUnit\Framework\TestCase;
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

	public function getFieldsCreate() : array {
		return [
			[
				[
					'price'    => 1,
					'currency' => Currency::CZK,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				]
			],
			[
				[
					'price'    => 600,
					'currency' => Currency::USD,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				]
			],
			[
				[
					'price'    => 0.1,
					'currency' => Currency::EUR,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				]
			],
			[
				[
					'price'    => 100,
					'currency' => Currency::HUF,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				]
			],
			[
				[
					'price'    => 5,
					'currency' => Currency::RON,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				]
			],
			[
				[
					'price'    => 0.5,
					'currency' => Currency::NOK,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				]
			],
			[
				[
					'price'    => 0.5,
					'currency' => Currency::SEK,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				]
			],
			[
				[
					'price'          => 60,
					'currency'       => Currency::GBP,
					'label'          => 'a',
					'refId'          => '1234',
					'email'          => 'test@email.cz',
					'expirationTime' => '10m',
					'eetData'        => '{"test":"aaaa"}',
				]
			],
			[
				[
					'price'          => 60,
					'currency'       => Currency::GBP,
					'label'          => 'abcddadiusldwqpd',
					'refId'          => '1234',
					'email'          => 'test@email.cz',
					'expirationTime' => '5d',
					'eetData'        => [
						'test' => 'aaa',
					],
					'lang'           => Lang::EN,
				]
			],
			[
				[
					'price'          => 60,
					'currency'       => Currency::GBP,
					'label'          => 'abcddadiusldwqpd',
					'refId'          => '1234',
					'email'          => 'test@email.cz',
					'expirationTime' => '655h',
					'eetData'        => [
						'test' => 'aaa',
					],
					'name'           => 'asdjasda',
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
	public function testPaymentCreation(array $fields) : void {
		/** @var Payment $payment */
		$payment = $this->container->getService('comgate.payment');

		foreach ($fields as $key => $value) {
			$payment->$key = $value;
		}

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
	}

}
