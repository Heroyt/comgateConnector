<?php

namespace Heroyt\ComGate\Exceptions;

use Heroyt\ComGate\Response\ReturnCodes;
use Psr\Http\Message\ResponseInterface;

class ApiResponseException extends ApiException
{

	public function __construct(
		public ResponseInterface $response,
		array                    $data = [],
	) {
		$message = 'API request failed';
		$code = $this->response->getStatusCode();

		// Try to parse information from the response
		if (isset($data['code'])) {
			$code = (int) $data['code'];
		}
		if (isset($data['message'])) {
			$message .= ': '.$data['message'];
		}

		parent::__construct($message, $code);
	}

	/**
	 * Get message based on the received return code
	 *
	 * @return string
	 */
	public function getErrorFromResponseCode() : string {
		/** @var ReturnCodes|null $returnCode */
		$returnCode = ReturnCodes::tryFrom($this->code);
		if (!isset($returnCode)) {
			return '';
		}
		return $returnCode->getMessage();
	}

}