<?php

namespace Heroyt\ComGate\Payment\Actions;

use Heroyt\ComGate\ConnectionInterface;
use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Exceptions\ApiResponseException;
use Heroyt\ComGate\Exceptions\ValidationException;
use Heroyt\ComGate\Payment\Currency;
use Heroyt\ComGate\Payment\Payment;
use Heroyt\ComGate\Response\ResponseParser;
use Heroyt\ComGate\Response\ReturnCodes;
use Nette\Utils\Validators;

class CreatePaymentAction implements PaymentActionInterface
{

	public const PATH = '/create';

	public function __construct(
		private readonly Payment $payment
	) {
	}

	/**
	 * Send a request and process its results
	 *
	 * @param ConnectionInterface $connection
	 *
	 * @return mixed
	 *
	 * @throws ApiException
	 * @throws ApiResponseException
	 * @throws ValidationException
	 */
	public function process(ConnectionInterface $connection) : string {
		// Validate all mandatory fields and their values
		$this->validate();

		$response = $connection->post($this::PATH, $this->getData());

		// Validate response
		$data = ResponseParser::getData($response);

		if (!is_array($data)) {
			throw new ApiException('The received data is invalid.');
		}
		// General API error
		if (((int) ($data['code'] ?? 0)) !== ReturnCodes::OK->value || $response->getStatusCode() > 399) {
			throw new ApiResponseException($response, $data);
		}
		// Invalid received data
		if (empty($data['transId'])) {
			throw new ApiException('Missing or invalid transaction ID received from the API.');
		}
		if (empty($data['redirect']) || !Validators::isUrl($data['redirect'])) {
			throw new ApiException('Missing or invalid redirect URL received from the API. "'.($data['redirect'] ?? '').'"');
		}

		$this->payment->transId = $data['transId'];

		return $data['redirect'];
	}

	/**
	 * Validate data before sending
	 *
	 * @return void
	 *
	 * @throws ValidationException
	 */
	public function validate() : void {
		/** @noinspection JsonEncodingApiUsageInspection */
		match (true) {
			// Check mandatory fields
			!isset($this->payment->price) => throw new ValidationException('Missing required field: price'),
			!isset($this->payment->currency) => throw new ValidationException('Missing required field: currency'),
			!isset($this->payment->label) => throw new ValidationException('Missing required field: label'),
			!isset($this->payment->refId) => throw new ValidationException('Missing required field: refId'),
			empty($this->payment->method) => throw new ValidationException('Missing required field: method'),
			empty($this->payment->email) => throw new ValidationException('Missing required field: email'),

			// Check valid values
			!Validators::isEmail($this->payment->email) => throw new ValidationException('Invalid value: email'),
			empty(trim($this->payment->label)) || strlen(trim($this->payment->label)) > 16 => throw new ValidationException('Invalid value: label. Must be between 1 and 16 characters long'),
			!empty($this->payment->expirationTime) && preg_match('/^\d+[mhd]$/', $this->payment->expirationTime) !== 1 => throw new ValidationException('Invalid value: expirationTime'),
			isset($this->payment->eetData) && is_string($this->payment->eetData) && @json_decode($this->payment->eetData, true) === null && json_last_error() !== JSON_ERROR_NONE => throw new ValidationException('Invalid value: eetData. Must be a valid JSON object.'),

			// Check minimum price based on currency
			$this->payment->currency === Currency::EUR && $this->payment->price < 0.1 => throw new ValidationException('Invalid price: Minimum amount is 0.1 '.$this->payment->currency->value),
			$this->payment->currency === Currency::HUF && $this->payment->price < 100 => throw new ValidationException('Invalid price: Minimum amount is 100 '.$this->payment->currency->value),
			$this->payment->currency === Currency::RON && $this->payment->price < 5 => throw new ValidationException('Invalid price: Minimum amount is 5 '.$this->payment->currency->value),
			in_array($this->payment->currency, [Currency::NOK, Currency::SEK], true) && $this->payment->price < 0.5 => throw new ValidationException('Invalid price: Minimum amount is 0.5 '.$this->payment->currency->value),
			!in_array($this->payment->currency, [Currency::EUR, Currency::HUF, Currency::RON, Currency::NOK, Currency::SEK], true) && $this->payment->price < 1 => throw new ValidationException('Invalid price: Minimum amount is 1 '.$this->payment->currency->value),

			default => 0,
		};
	}

	/**
	 * Returns all the data as an array.
	 *
	 * @post The data is formatted
	 *
	 * @return array
	 */
	public function getData() : array {
		// All mandatory fields
		$data = [
			'price'       => $this->payment->price,
			'curr'        => $this->payment->currency->value,
			'label'       => trim($this->payment->label),
			'refId'       => trim($this->payment->refId),
			'method'      => trim($this->payment->method),
			'email'       => trim($this->payment->email),
			'prepareOnly' => $this->payment->prepareOnly,
		];

		// Add optional fields
		if (isset($this->payment->country)) {
			$data['country'] = $this->payment->country->value;
		}
		if (isset($this->payment->account)) {
			$data['account'] = trim($this->payment->account);
		}
		if (isset($this->payment->phone)) {
			$data['phone'] = trim($this->payment->phone);
		}
		if (isset($this->payment->name)) {
			$data['name'] = trim($this->payment->name);
		}
		if (isset($this->payment->lang)) {
			$data['lang'] = $this->payment->lang->value;
		}
		if (isset($this->payment->preauth)) {
			$data['preauth'] = $this->payment->preauth;
		}
		if (isset($this->payment->initRecurring)) {
			$data['initRecurring'] = $this->payment->initRecurring;
		}
		if (isset($this->payment->verification)) {
			$data['verification'] = $this->payment->verification;
		}
		if (isset($this->payment->eetReport)) {
			$data['eetReport'] = $this->payment->eetReport;
		}
		if (isset($this->payment->eetData)) {
			$data['eetData'] = is_array($this->payment->eetData) ? json_encode($this->payment->eetData, JSON_THROW_ON_ERROR) : $this->payment->eetData;
		}
		if (isset($this->payment->embedded)) {
			$data['embedded'] = $this->payment->embedded;
		}
		if (!empty($this->payment->applePayPayload)) {
			$data['applePayPayload'] = base64_encode($this->payment->applePayPayload);
		}
		if (!empty($this->payment->expirationTime)) {
			$data['expirationTime'] = $this->payment->expirationTime;
		}
		if (isset($this->payment->dynamicExpiration)) {
			$data['dynamicExpiration'] = $this->payment->dynamicExpiration;
		}

		return $data;
	}
}