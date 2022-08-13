<?php

namespace Testing\ComGate;

class Logger extends \Psr\Log\AbstractLogger
{

	public static array $lines = [];

	/**
	 * @inheritDoc
	 */
	public function log($level, \Stringable|string $message, array $context = []) : void {
		self::$lines[] = [
			'level'   => $level,
			'message' => $message,
			'context' => $context,
		];
	}
}