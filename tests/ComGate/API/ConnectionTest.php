<?php

namespace Testing\ComGate\API;

use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Payment\Currency;
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
		echo $redirect.PHP_EOL;
	}

}
