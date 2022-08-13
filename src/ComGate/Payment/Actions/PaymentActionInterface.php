<?php

namespace Heroyt\ComGate\Payment\Actions;

use Heroyt\ComGate\ConnectionInterface;
use Heroyt\ComGate\Exceptions\ApiException;
use Heroyt\ComGate\Exceptions\ValidationException;
use Heroyt\ComGate\Payment\Payment;

interface PaymentActionInterface
{

	public function __construct(Payment $payment);

	/**
	 * Send a request and process its results
	 *
	 * @param ConnectionInterface $connection
	 *
	 * @return mixed
	 *
	 * @throws ApiException
	 */
	public function process(ConnectionInterface $connection) : mixed;

	/**
	 * Returns all the data as an array.
	 *
	 * @post The data is formatted
	 *
	 * @return array
	 */
	public function getData() : array;

	/**
	 * Validate data before sending
	 *
	 * @return void
	 *
	 * @throws ValidationException
	 */
	public function validate() : void;

}