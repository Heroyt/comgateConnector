<?php

namespace Heroyt\ComGate\Payment;

use Heroyt\ComGate\ConnectionInterface;
use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Exceptions\ApiResponseException;
use Heroyt\ComGate\Exceptions\ValidationException;
use Heroyt\ComGate\Payment\Actions\CancelPreauthPaymentAction;
use Heroyt\ComGate\Payment\Actions\CapturePreauthPaymentAction;
use Heroyt\ComGate\Payment\Actions\CreatePaymentAction;

class Payment
{

	public const CREATE          = '/create';
	public const CAPTURE_PREAUTH = '/capturePreauth';

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
		return (new CreatePaymentAction($this))->process($this->connection);
	}

	/**
	 * @throws ApiException
	 * @throws ApiResponseException
	 * @throws ValidationException
	 */
	public function cancelPreauth() : void {
		(new CancelPreauthPaymentAction($this))->process($this->connection);
	}

	/**
	 * @throws ApiException
	 * @throws ApiResponseException
	 * @throws ValidationException
	 */
	public function capturePreauth(?float $amount = null) : void {
		$action = new CapturePreauthPaymentAction($this);
		if ($amount !== null) {
			$action->amount = $amount;
		}
		$action->process($this->connection);
	}

}