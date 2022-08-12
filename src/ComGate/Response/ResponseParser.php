<?php

namespace Heroyt\ComGate\Response;

use Psr\Http\Message\ResponseInterface;

class ResponseParser
{

	public static function getData(ResponseInterface $response) : null|array|string {
		if (!$response->hasHeader('Content-Type')) {
			return null;
		}

		if (str_contains($response->getHeader('Content-Type')[0] ?? '', 'application/json')) {
			return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
		}
		if (str_contains($response->getHeader('Content-Type')[0], 'application/x-www-form-urlencoded')) {
			parse_str($response->getBody()->getContents(), $data);
			return $data;
		}

		return $response->getBody()->getContents();
	}

}