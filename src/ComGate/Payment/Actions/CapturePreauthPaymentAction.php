<?php

namespace Heroyt\ComGate\Payment\Actions;

use Heroyt\ComGate\ConnectionInterface;
use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Exceptions\ApiResponseException;
use Heroyt\ComGate\Exceptions\ValidationException;
use Heroyt\ComGate\Payment\Payment;
use Heroyt\ComGate\Response\ResponseParser;
use Heroyt\ComGate\Response\ReturnCodes;

class CapturePreauthPaymentAction implements PaymentActionInterface
{

	public const PATH = '/capturePreauth';

	public float $amount;

	public function __construct(
		private readonly Payment $payment
	) {
	}

	/**
	 * @inheritDoc
	 * @throws ValidationException
	 */
	public function process(ConnectionInterface $connection) : bool {
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

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function validate() : void {
		match (true) {
			empty($this->payment->transId) => throw new ValidationException('Missing required field: transId'),
			isset($this->amount) && ($this->amount <= 0 || (isset($this->payment->price) && $this->amount > $this->payment->price)) => throw new ValidationException('Invalid value: amount. Must be grater than 0 and less then the total transaction amount.'),
			default => 0,
		};
	}

	/**
	 * @inheritDoc
	 */
	public function getData() : array {
		$data = [
			'transId' => $this->payment->transId,
		];

		if (isset($this->amount)) {
			$data['amount'] = $this->amount;
		}

		return $data;
	}
}