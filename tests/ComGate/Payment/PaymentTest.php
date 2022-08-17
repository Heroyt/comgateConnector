<?php

namespace Testing\ComGate\Payment;

use Heroyt\ComGate\ConnectionInterface;
use Heroyt\ComGate\Payment\Actions\CancelPreauthPaymentAction;
use Heroyt\ComGate\Payment\Actions\CapturePreauthPaymentAction;
use Heroyt\ComGate\Payment\Actions\CreatePaymentAction;
use Heroyt\ComGate\Payment\Country;
use Heroyt\ComGate\Payment\Currency;
use Heroyt\ComGate\Payment\Lang;
use Heroyt\ComGate\Payment\Payment;
use Heroyt\ComGate\Payment\State;
use PHPUnit\Framework\TestCase;
use Testing\ComGate\Connection;

class PaymentTest extends TestCase
{

	private ConnectionInterface $connection;

	public function __construct(?string $name = null, array $data = [], $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$this->connection = new Connection();
	}

	public function testGetCreateData() : void {
		$payment = new Payment($this->connection);

		$data = [
			'price'       => 1.1,
			'curr'        => Currency::CZK->value,
			'label'       => 'ahoj',
			'refId'       => '1234',
			'method'      => 'ALL',
			'email'       => 'test@email.cz',
			'prepareOnly' => true,
		];

		$payment->price = 1.1;
		$payment->currency = Currency::CZK;
		$payment->label = ' ahoj';
		$payment->refId = '1234   ';
		$payment->email = 'test@email.cz';

		$action = new CreatePaymentAction($payment);

		// Basic data
		self::assertEquals($data, $action->getData());

		// Add optional data
		$data['country'] = Country::CZ->value;
		$payment->country = Country::CZ;
		self::assertEquals($data, $action->getData());
		$data['account'] = '1234567890';
		$payment->account = '1234567890';
		self::assertEquals($data, $action->getData());
		$data['phone'] = '123456789';
		$payment->phone = '123456789';
		self::assertEquals($data, $action->getData());
		$data['preauth'] = true;
		$payment->preauth = true;
		self::assertEquals($data, $action->getData());
		$data['initRecurring'] = true;
		$payment->initRecurring = true;
		self::assertEquals($data, $action->getData());
		$data['verification'] = true;
		$payment->verification = true;
		self::assertEquals($data, $action->getData());
		$data['eetReport'] = true;
		$payment->eetReport = true;
		self::assertEquals($data, $action->getData());
		$data['eetData'] = '{"hello":"world"}';
		$payment->eetData = ['hello' => 'world'];
		self::assertEquals($data, $action->getData());
		$data['embedded'] = true;
		$payment->embedded = true;
		self::assertEquals($data, $action->getData());
		$data['applePayPayload'] = base64_encode('test');
		$payment->applePayPayload = 'test';
		self::assertEquals($data, $action->getData());
		$data['expirationTime'] = '10m';
		$payment->expirationTime = '10m';
		self::assertEquals($data, $action->getData());
		$data['dynamicExpiration'] = true;
		$payment->dynamicExpiration = true;
		self::assertEquals($data, $action->getData());
		$data['lang'] = Lang::EN->value;
		$payment->lang = Lang::EN;
		self::assertEquals($data, $action->getData());
		$data['name'] = 'asdasd';
		$payment->name = 'asdasd';
		self::assertEquals($data, $action->getData());
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
	public function testCreate(array $fields) : void {
		$payment = new Payment($this->connection);

		foreach ($fields as $key => $value) {
			$payment->$key = $value;
		}

		$redirect = $payment->create();
		self::assertNotEmpty($payment->transId);
		self::assertEquals('AB12-EF34-IJ56', $payment->transId);
		self::assertEquals(urldecode('https%3A%2F%2Fpayments.comgate.cz%2Fclient%2Finstructions%2Findex%3Fid%3DABCDEFGHI'), $redirect);
	}

	public function getFieldsInvalidCreate() : array {
		return [
			[
				[
					'price'    => 100,
					'currency' => Currency::CZK,
					'label'    => 'invalidData',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'The received data is invalid.'
			],
			[
				[
					'price'    => 100,
					'currency' => Currency::CZK,
					'label'    => 'invalid1',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'API request failed: DBerror'
			],
			[
				[
					'price'    => 100,
					'currency' => Currency::CZK,
					'label'    => 'invalid2',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Missing or invalid transaction ID received from the API.'
			],
			[
				[
					'price'    => 100,
					'currency' => Currency::CZK,
					'label'    => 'invalid3',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Missing or invalid redirect URL received from the API. ""'
			],
			[
				[
					'price'    => 100,
					'currency' => Currency::CZK,
					'label'    => 'invalid4',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Missing or invalid redirect URL received from the API. "invalidUrl"'
			],
			[
				[
					'currency' => Currency::CZK,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Missing required field: price'
			],
			[
				[
					'price' => 1.1,
					'label' => 'ahoj',
					'refId' => '1234',
					'email' => 'test@email.cz',
				],
				'Missing required field: currency'
			],
			[
				[
					'price'    => 1.1,
					'currency' => Currency::CZK,
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Missing required field: label'
			],
			[
				[
					'price'    => 1.1,
					'currency' => Currency::CZK,
					'label'    => 'ahoj',
					'email'    => 'test@email.cz',
				],
				'Missing required field: refId'
			],
			[
				[
					'price'    => 1.1,
					'currency' => Currency::CZK,
					'label'    => 'ahoj',
					'refId'    => '1234',
				],
				'Missing required field: email'
			],
			[
				[
					'price'    => 1.1,
					'currency' => Currency::CZK,
					'label'    => 'ahoj',
					'refId'    => '1234',
					'email'    => 'test',
				],
				'Invalid value: email'
			],
			[
				[
					'price'    => 1.1,
					'currency' => Currency::CZK,
					'label'    => 'dabishdbahdbasjdhabjwzbasjhdbasjdhbasd',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Invalid value: label. Must be between 1 and 16 characters long'
			],
			[
				[
					'price'    => 1.1,
					'currency' => Currency::CZK,
					'label'    => '',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Invalid value: label. Must be between 1 and 16 characters long'
			],
			[
				[
					'price'          => 1.1,
					'currency'       => Currency::CZK,
					'label'          => 'test',
					'refId'          => '1234',
					'email'          => 'test@email.cz',
					'expirationTime' => '20mm'
				],
				'Invalid value: expirationTime'
			],
			[
				[
					'price'          => 1.1,
					'currency'       => Currency::CZK,
					'label'          => 'test',
					'refId'          => '1234',
					'email'          => 'test@email.cz',
					'expirationTime' => 'm'
				],
				'Invalid value: expirationTime'
			],
			[
				[
					'price'    => 1.1,
					'currency' => Currency::CZK,
					'label'    => 'test',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
					'eetData'  => '{"aaaaa": "vvv',
				],
				'Invalid value: eetData. Must be a valid JSON object.'
			],
			[
				[
					'price'    => 0.5,
					'currency' => Currency::CZK,
					'label'    => 'test',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Invalid price: Minimum amount is 1 CZK'
			],
			[
				[
					'price'    => 0,
					'currency' => Currency::EUR,
					'label'    => 'test',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Invalid price: Minimum amount is 0.1 EUR'
			],
			[
				[
					'price'    => 50,
					'currency' => Currency::HUF,
					'label'    => 'test',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Invalid price: Minimum amount is 100 HUF'
			],
			[
				[
					'price'    => 4,
					'currency' => Currency::RON,
					'label'    => 'test',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Invalid price: Minimum amount is 5 RON'
			],
			[
				[
					'price'    => 0.1,
					'currency' => Currency::SEK,
					'label'    => 'test',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Invalid price: Minimum amount is 0.5 SEK'
			],
			[
				[
					'price'    => 0.1,
					'currency' => Currency::NOK,
					'label'    => 'test',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Invalid price: Minimum amount is 0.5 NOK'
			],
			[
				[
					'price'    => 0.1,
					'currency' => Currency::USD,
					'label'    => 'test',
					'refId'    => '1234',
					'email'    => 'test@email.cz',
				],
				'Invalid price: Minimum amount is 1 USD'
			],
		];
	}

	/**
	 * @dataProvider getFieldsInvalidCreate
	 *
	 * @param array  $fields
	 * @param string $message
	 *
	 * @return void
	 */
	public function testCreateInvalid(array $fields, string $message) : void {
		$payment = new Payment($this->connection);

		foreach ($fields as $key => $value) {
			$payment->$key = $value;
		}

		$this->expectExceptionMessage($message);

		$payment->create();
	}

	public function testGetCapturePreauthData() : void {
		$payment = new Payment($this->connection);

		$data = [
			'transId' => 'AB12-EF34-IJ56',
		];

		$payment->transId = 'AB12-EF34-IJ56';

		// Should be omitted
		$payment->label = 'ahoj';

		$action = new CapturePreauthPaymentAction($payment);

		// Basic data
		self::assertEquals($data, $action->getData());

		// Add optional data
		$action->amount = 20;
		$data['amount'] = 20;
		self::assertEquals($data, $action->getData());
	}

	public function testCapturePreauth() : void {
		$payment = new Payment($this->connection);

		$payment->transId = 'AB12-EF34-IJ56';

		$payment->capturePreauth();

		$payment->capturePreauth(20);

		$payment->price = 50;
		$payment->capturePreauth(20);

		// Manual send
		$action = new CapturePreauthPaymentAction($payment);
		self::assertTrue($action->process($this->connection));
	}

	public function getFieldsInvalidCapturePreauth() : array {
		return [
			[
				[],
				null,
				'Missing required field: transId',
			],
			[
				['transId' => 'ABCD'],
				0.0,
				'Invalid value: amount. Must be grater than 0 and less then the total transaction amount.',
			],
			[
				['transId' => 'ABCD'],
				-10.0,
				'Invalid value: amount. Must be grater than 0 and less then the total transaction amount.',
			],
			[
				['transId' => 'ABCD', 'price' => 10],
				20,
				'Invalid value: amount. Must be grater than 0 and less then the total transaction amount.',
			],
			[
				['transId' => 'ABCD'],
				null,
				'The received data is invalid.',
				'invalidData',
			],
			[
				['transId' => 'ABCD'],
				null,
				'API request failed: DBerror',
				'invalid',
			],
		];
	}

	/**
	 * @dataProvider getFieldsInvalidCapturePreauth
	 *
	 * @return void
	 */
	public function testCapturePreauthInvalid(array $fieldsPayment, ?float $amount, string $message, string $switch = '') : void {
		$payment = new Payment($this->connection);
		$this->connection->switch = $switch;

		foreach ($fieldsPayment as $key => $value) {
			$payment->$key = $value;
		}

		$this->expectExceptionMessage($message);

		$payment->capturePreauth($amount);
	}

	public function testGetCancelPreauthData() : void {
		$payment = new Payment($this->connection);

		$data = [
			'transId' => 'AB12-EF34-IJ56',
		];

		$payment->transId = 'AB12-EF34-IJ56';

		// Should be omitted
		$payment->label = 'ahoj';

		$action = new CancelPreauthPaymentAction($payment);

		// Basic data
		self::assertEquals($data, $action->getData());
	}

	public function testCancelPreauth() : void {
		$payment = new Payment($this->connection);

		$payment->transId = 'AB12-EF34-IJ56';

		$payment->cancelPreauth();

		// Manual send
		$action = new CancelPreauthPaymentAction($payment);
		self::assertTrue($action->process($this->connection));
	}

	public function getFieldsInvalidCancelPreauth() : array {
		return [
			[
				[],
				'Missing required field: transId',
			],
			[
				['transId' => 'ABCD'],
				'The received data is invalid.',
				'invalidData',
			],
			[
				['transId' => 'ABCD'],
				'API request failed: DBerror',
				'invalid',
			],
		];
	}

	/**
	 * @dataProvider getFieldsInvalidCancelPreauth
	 *
	 * @return void
	 */
	public function testCancelPreauthInvalid(array $fieldsPayment, string $message, string $switch = '') : void {
		$payment = new Payment($this->connection);
		$this->connection->switch = $switch;

		foreach ($fieldsPayment as $key => $value) {
			$payment->$key = $value;
		}

		$this->expectExceptionMessage($message);

		$payment->cancelPreauth();
	}

	public function getFieldsGetStatus() : array {
		return [
			[
				[
					'transId' => 'AB12',
				],
				[
					'state'    => State::PENDING,
					'price'    => 100,
					'currency' => Currency::CZK,
					'label'    => 'test',
					'refId'    => '123',
					'email'    => 'test@test.cz'
				],
			],
			[
				[
					'transId' => '1234',
				],
				[
					'state'    => State::AUTHORIZED,
					'price'    => 100,
					'currency' => Currency::CZK,
					'label'    => 'test',
					'refId'    => '123',
					'email'    => 'test@test.cz',
					'payerId'  => '999',
					'method'   => 'ALL',
				],
			],
			[
				[
					'transId' => 'ABCD',
				],
				[
					'state'     => State::CANCELLED,
					'price'     => 80,
					'currency'  => Currency::CZK,
					'label'     => 'test',
					'refId'     => '123',
					'email'     => 'test@test.cz',
					'payerId'   => '999',
					'method'    => 'ALL',
					'account'   => 'abcdefg',
					'phone'     => '123456789',
					'name'      => 'test',
					'payerName' => 'TestTestovič',
					'payerAcc'  => '888888',
					'fee'       => '20',
					'eetData'   => ['key' => 'value'],
				],
			],
		];
	}

	/**
	 * @dataProvider getFieldsGetStatus
	 *
	 * @param array $fields
	 * @param array $expected
	 *
	 * @return void
	 */
	public function testGetStatus(array $fields, array $expected) : void {
		$payment = new Payment($this->connection);

		foreach ($fields as $key => $value) {
			$payment->$key = $value;
		}

		$payment->getStatus();
		foreach ($expected as $key => $value) {
			self::assertEquals($value, $payment->$key);
		}
	}


	public function getFieldsGetStatusInvalid() : array {
		return [
			[
				[
					'transId' => 'AB12',
				],
				'The received data is invalid.',
				'invalidData',
			],
			[
				[
				],
				'Missing required field: transId',
			],
			[
				['transId' => 'invalid'],
				'API request failed: DBerror',
			],
		];
	}

	/**
	 * @dataProvider getFieldsGetStatusInvalid
	 *
	 * @param array  $fields
	 * @param string $message
	 * @param string $switch
	 *
	 * @return void
	 */
	public function testGetStatusInvalid(array $fields, string $message, string $switch = '') : void {
		$payment = new Payment($this->connection);
		$this->connection->switch = $switch;

		foreach ($fields as $key => $value) {
			$payment->$key = $value;
		}

		$this->expectExceptionMessage($message);
		$payment->getStatus();
	}

}
