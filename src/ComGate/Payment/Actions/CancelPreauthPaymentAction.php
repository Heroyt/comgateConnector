<?php

namespace Heroyt\ComGate\Payment\Actions;

use Heroyt\ComGate\ConnectionInterface;
use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Exceptions\ApiResponseException;
use Heroyt\ComGate\Exceptions\ValidationException;
use Heroyt\ComGate\Payment\Payment;
use Heroyt\ComGate\Response\ResponseParser;
use Heroyt\ComGate\Response\ReturnCodes;

class CancelPreauthPaymentAction implements PaymentActionInterface
{

	public const PATH = '/cancelPreauth';

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