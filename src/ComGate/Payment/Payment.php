<?php

namespace Heroyt\ComGate\Payment;

use Heroyt\ComGate\ConnectionInterface;
use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Exceptions\ApiResponseException;
use Heroyt\ComGate\Exceptions\ValidationException;
use Heroyt\ComGate\Response\ResponseParser;
use Heroyt\ComGate\Response\ReturnCodes;
use Nette\Utils\Validators;

class Payment
{

	public const CREATE = '/create';

	public string       $refId;
	public Country      $country;
	public float        $price;
	public Currency     $currency;
	public string       $label;
	public string       $method            = 'ALL';
	public string       $account;
	public string       $email;
	public string       $phone;
	public string       $name;
	public Lang         $lang;
	public bool         $prepareOnly       = true;
	public ?bool        $preauth           = null;
	public ?bool        $initRecurring     = null;
	public ?bool        $verification      = null;
	public ?bool        $eetReport         = null;
	public string|array $eetData;
	public ?bool        $embedded          = null;
	public string       $applePayPayload   = '';
	public string       $expirationTime    = '';
	public ?bool        $dynamicExpiration = null;
	public string       $transId;

	public function __construct(
		private readonly ConnectionInterface $connection,
		public readonly State                $state = State::PENDING,
	) {
	}

	/**
	 * @return string Redirect URL
	 * @throws ApiException
	 * @throws ApiResponseException
	 * @throws ValidationException
	 */
	public function create() : string {
		// Validate all mandatory fields and their values
		$this->validateCreate();

		$response = $this->connection->post($this::CREATE, $this->getCreateData());

		// Validate response
		$data = ResponseParser::getData($response);

		if (!is_array($data)) {
			throw new ApiException('The received data is invalid.');
		}
		// General API error
		if (((int) $data['code']) !== ReturnCodes::OK->value || $response->getStatusCode() > 399) {
			throw new ApiResponseException($response, $data);
		}
		// Invalid received data
		if (empty($data['transId'])) {
			throw new ApiException('Missing or invalid transaction ID received from the API.');
		}
		if (empty($data['redirect']) || !Validators::isUrl($data['redirect'])) {
			throw new ApiException('Missing or invalid redirect URL received from the API. "'.($data['redirect'] ?? '').'"');
		}

		$this->transId = $data['transId'];

		return $data['redirect'];
	}

	/**
	 * Validate all fields
	 *
	 * @throws ValidationException
	 */
	protected function validateCreate() : void {
		/** @noinspection JsonEncodingApiUsageInspection */
		match (true) {
			// Check mandatory fields
			!isset($this->price) => throw new ValidationException('Missing required field: price'),
			!isset($this->currency) => throw new ValidationException('Missing required field: currency'),
			!isset($this->label) => throw new ValidationException('Missing required field: label'),
			!isset($this->refId) => throw new ValidationException('Missing required field: refId'),
			empty($this->method) => throw new ValidationException('Missing required field: method'),
			empty($this->email) => throw new ValidationException('Missing required field: email'),

			// Check valid values
			!Validators::isEmail($this->email) => throw new ValidationException('Invalid value: email'),
			empty(trim($this->label)) || strlen(trim($this->label)) > 16 => throw new ValidationException('Invalid value: label. Must be between 1 and 16 characters long'),
			!empty($this->expirationTime) && preg_match('/^\d+[mhd]$/', $this->expirationTime) !== 1 => throw new ValidationException('Invalid value: expirationTime'),
			isset($this->eetData) && is_string($this->eetData) && @json_decode($this->eetData, true) === null && json_last_error() !== JSON_ERROR_NONE => throw new ValidationException('Invalid value: eetData. Must be a valid JSON object.'),

			// Check minimum price based on currency
			$this->currency === Currency::EUR && $this->price < 0.1 => throw new ValidationException('Invalid price: Minimum amount is 0.1 '.$this->currency->value),
			$this->currency === Currency::HUF && $this->price < 100 => throw new ValidationException('Invalid price: Minimum amount is 100 '.$this->currency->value),
			$this->currency === Currency::RON && $this->price < 5 => throw new ValidationException('Invalid price: Minimum amount is 5 '.$this->currency->value),
			in_array($this->currency, [Currency::NOK, Currency::SEK], true) && $this->price < 0.5 => throw new ValidationException('Invalid price: Minimum amount is 0.5 '.$this->currency->value),
			!in_array($this->currency, [Currency::EUR, Currency::HUF, Currency::RON, Currency::NOK, Currency::SEK], true) && $this->price < 1 => throw new ValidationException('Invalid price: Minimum amount is 1 '.$this->currency->value),

			default => 0,
		};
	}

	/**
	 * Get formatted data for the create payment method
	 *
	 * @return array
	 */
	public function getCreateData() : array {
		// All mandatory fields
		$data = [
			'price'       => $this->price,
			'curr'        => $this->currency->value,
			'label'       => trim($this->label),
			'refId'       => trim($this->refId),
			'method'      => trim($this->method),
			'email'       => trim($this->email),
			'prepareOnly' => $this->prepareOnly,
		];

		// Add optional fields
		if (isset($this->country)) {
			$data['country'] = $this->country->value;
		}
		if (isset($this->account)) {
			$data['account'] = trim($this->account);
		}
		if (isset($this->phone)) {
			$data['phone'] = trim($this->phone);
		}
		if (isset($this->name)) {
			$data['name'] = trim($this->name);
		}
		if (isset($this->lang)) {
			$data['lang'] = $this->lang->value;
		}
		if (isset($this->preauth)) {
			$data['preauth'] = $this->preauth;
		}
		if (isset($this->initRecurring)) {
			$data['initRecurring'] = $this->initRecurring;
		}
		if (isset($this->verification)) {
			$data['verification'] = $this->verification;
		}
		if (isset($this->eetReport)) {
			$data['eetReport'] = $this->eetReport;
		}
		if (isset($this->eetData)) {
			$data['eetData'] = is_array($this->eetData) ? json_encode($this->eetData, JSON_THROW_ON_ERROR) : $this->eetData;
		}
		if (isset($this->embedded)) {
			$data['embedded'] = $this->embedded;
		}
		if (!empty($this->applePayPayload)) {
			$data['applePayPayload'] = base64_encode($this->applePayPayload);
		}
		if (!empty($this->expirationTime)) {
			$data['expirationTime'] = $this->expirationTime;
		}
		if (isset($this->dynamicExpiration)) {
			$data['dynamicExpiration'] = $this->dynamicExpiration;
		}

		return $data;
	}

}