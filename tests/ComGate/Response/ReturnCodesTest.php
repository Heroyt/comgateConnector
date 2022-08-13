<?php

namespace Testing\ComGate\Response;

use GuzzleHttp\Psr7\Response;
use Heroyt\ComGate\Exceptions\ApiResponseException;
use Heroyt\ComGate\Response\ReturnCodes;
use PHPUnit\Framework\TestCase;

class ReturnCodesTest extends TestCase
{

	public function getCodes() : array {
		/** @var ReturnCodes[] $cases */
		$cases = ReturnCodes::cases();
		$data = [];
		foreach ($cases as $case) {
			$data[] = [$case];
		}
		return $data;
	}

	/**
	 * @dataProvider getCodes
	 *
	 * @param ReturnCodes $code
	 *
	 * @return void
	 */
	public function testGetMessage(ReturnCodes $code) : void {
		self::assertNotEquals('General error', $code->getMessage());
	}

	public function getExceptions() : array {
		/** @var ReturnCodes[] $cases */
		$cases = ReturnCodes::cases();
		$data = [];
		foreach ($cases as $case) {
			$response = new Response(200);
			$data[] = [
				new ApiResponseException($response, ['code' => $case->value]),
				$case
			];
		}
		return $data;
	}

	/**
	 * @dataProvider getExceptions
	 *
	 * @param ApiResponseException $exception
	 * @param ReturnCodes          $code
	 *
	 * @return void
	 */
	public function testExceptionMessage(ApiResponseException $exception, ReturnCodes $code) : void {
		self::assertEquals($code->getMessage(), $exception->getErrorFromResponseCode());
	}

	public function testExceptionMessageInvalid() : void {
		self::assertEquals('', (new ApiResponseException(new Response(), ['code' => 123]))->getErrorFromResponseCode());
	}
}
