<?php

namespace Testing\ComGate\Response;

use GuzzleHttp\Psr7\Response;
use Heroyt\ComGate\Response\ResponseParser;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseParserTest extends TestCase
{

	/**
	 * @dataProvider getData
	 *
	 * @param ResponseInterface $response
	 * @param string|array|null $expected
	 *
	 * @return void
	 */
	public function testGetData(ResponseInterface $response, string|array|null $expected) : void {
		self::assertEquals($expected, ResponseParser::getData($response));
	}

	public function getData() : array {
		return [
			[
				new Response(200, ['Content-Type' => 'application/x-www-form-urlencoded'], 'code=0&message=OK&transId=AB12-EF34-IJ56&redirect=https%3A%2F%2Fpayments.comgate.cz%2Fclient%2Finstructions%2Findex%3Fid%3DABCDEFGHI'),
				[
					'code'     => '0',
					'message'  => 'OK',
					'transId'  => 'AB12-EF34-IJ56',
					'redirect' => urldecode('https%3A%2F%2Fpayments.comgate.cz%2Fclient%2Finstructions%2Findex%3Fid%3DABCDEFGHI'),
				]
			],
			[
				new Response(200, ['Content-Type' => 'application/json'], '{"code": 0, "message": "OK", "transId": "AB12-EF34-IJ56"}'),
				[
					'code'    => 0,
					'message' => 'OK',
					'transId' => 'AB12-EF34-IJ56',
				]
			],
			[
				new Response(500, ['Content-Type' => 'application/json'], '{"code": 1101, "message": "ERROR"}'),
				[
					'code'    => 1101,
					'message' => 'ERROR',
				]
			],
			[
				new Response(500, ['Content-Type' => 'text/plain'], 'asdads'),
				'asdads'
			],
			[
				new Response(200, [], '{"code": 1101, "message": "ERROR"}'),
				null
			],
		];
	}
}
