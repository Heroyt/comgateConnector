<?php

namespace Testing\ComGate\Payment;

use Heroyt\ComGate\ConnectionInterface;
use Heroyt\ComGate\Payment\Country;
use Heroyt\ComGate\Payment\Currency;
use Heroyt\ComGate\Payment\Lang;
use Heroyt\ComGate\Payment\Payment;
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

		// Basic data
		self::assertEquals($data, $payment->getCreateData());

		// Add optional data
		$data['country'] = Country::CZ->value;
		$payment->country = Country::CZ;
		self::assertEquals($data, $payment->getCreateData());
		$data['account'] = '1234567890';
		$payment->account = '1234567890';
		self::assertEquals($data, $payment->getCreateData());
		$data['phone'] = '123456789';
		$payment->phone = '123456789';
		self::assertEquals($data, $payment->getCreateData());
		$data['preauth'] = true;
		$payment->preauth = true;
		self::assertEquals($data, $payment->getCreateData());
		$data['initRecurring'] = true;
		$payment->initRecurring = true;
		self::assertEquals($data, $payment->getCreateData());
		$data['verification'] = true;
		$payment->verification = true;
		self::assertEquals($data, $payment->getCreateData());
		$data['eetReport'] = true;
		$payment->eetReport = true;
		self::assertEquals($data, $payment->getCreateData());
		$data['eetData'] = '{"hello":"world"}';
		$payment->eetData = ['hello' => 'world'];
		self::assertEquals($data, $payment->getCreateData());
		$data['embedded'] = true;
		$payment->embedded = true;
		self::assertEquals($data, $payment->getCreateData());
		$data['applePayPayload'] = base64_encode('test');
		$payment->applePayPayload = 'test';
		self::assertEquals($data, $payment->getCreateData());
		$data['expirationTime'] = '10m';
		$payment->expirationTime = '10m';
		self::assertEquals($data, $payment->getCreateData());
		$data['dynamicExpiration'] = true;
		$payment->dynamicExpiration = true;
		self::assertEquals($data, $payment->getCreateData());
		$data['lang'] = Lang::EN->value;
		$payment->lang = Lang::EN;
		self::assertEquals($data, $payment->getCreateData());
		$data['name'] = 'asdasd';
		$payment->name = 'asdasd';
		self::assertEquals($data, $payment->getCreateData());
	}

	public function getFields() : array {
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
	 * @dataProvider getFields
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

	public function getFieldsInvalid() : array {
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
	 * @dataProvider getFieldsInvalid
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
}
