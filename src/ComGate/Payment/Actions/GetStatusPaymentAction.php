<?php

namespace Heroyt\ComGate\Payment\Actions;

use Heroyt\ComGate\ConnectionInterface;
use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Exceptions\ApiResponseException;
use Heroyt\ComGate\Exceptions\ValidationException;
use Heroyt\ComGate\Payment\Currency;
use Heroyt\ComGate\Payment\Payment;
use Heroyt\ComGate\Payment\State;
use Heroyt\ComGate\Response\ResponseParser;
use Heroyt\ComGate\Response\ReturnCodes;

class GetStatusPaymentAction implements PaymentActionInterface
{

	public const PATH = '/status';

	public function __construct(
		private readonly Payment $payment
	) {
	}

	/**
	 * @inheritDoc
	 * @throws ValidationException
	 */
	public function process(ConnectionInterface $connection) : State {
		// Validate request data
		$this->validate();

		$response = $connection->post($this::PATH, $this->getData());

		// Validate response
		$data = ResponseParser::getData($response);
		if (!is_array($data)) {
			throw new ApiException('The received data is invalid.');
		}
		// General API error
		if (((int) $data['code']) !== ReturnCodes::OK->value || $response->getStatusCode() > 399) {
			throw new ApiResponseException($response, $data);
		}

		// Set data from the API to the object
		$this->payment->price = $data['price'];
		$this->payment->currency = Currency::tryFrom($data['curr']);
		$this->payment->label = $data['label'];
		$this->payment->refId = $data['refId'];
		$this->payment->email = $data['email'];
		$this->payment->state = State::tryFrom($data['status']);
		if (isset($data['payerId'])) {
			$this->payment->payerId = $data['payerId'];
		}
		if (isset($data['method'])) {
			$this->payment->method = $data['method'];
		}
		if (isset($data['account'])) {
			$this->payment->account = $data['account'];
		}
		if (isset($data['phone'])) {
			$this->payment->phone = $data['phone'];
		}
		if (isset($data['name'])) {
			$this->payment->name = $data['name'];
		}
		if (isset($data['payerName'])) {
			$this->payment->payerName = $data['payerName'];
		}
		if (isset($data['payerAcc'])) {
			$this->payment->payerAcc = $data['payerAcc'];
		}
		if (isset($data['fee'])) {
			$this->payment->fee = $data['fee'];
		}
		if (isset($data['eetData'])) {
			$this->payment->eetData = json_decode($data['eetData'], true, 512, JSON_THROW_ON_ERROR);
		}

		return $this->payment->state;
	}

	/**
	 * @inheritDoc
	 */
	public function validate() : void {
		match (true) {
			empty($this->payment->transId) => throw new ValidationException('Missing required field: transId'),
			default => 0,
		};
	}

	/**
	 * @inheritDoc
	 */
	public function getData() : array {
		return [
			'transId' => $this->payment->transId,
		];
	}
}