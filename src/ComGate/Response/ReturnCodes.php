<?php

namespace Heroyt\ComGate\Response;

/**
 * @property int $value
 * @method static tryFrom(int $code)
 * @method static from(int $code)
 */
enum ReturnCodes : int
{

	// General
	case OK = 0;
	case UNKNOWN_ERROR = 1100;

	// General errors
	case LANGUAGE_ERROR = 1102;
	case PAYMENT_METHOD_ERROR = 1103;
	case PAYMENT_LOAD_ERROR = 1104;
	case PAYMENT_PRICE_ERROR = 1107;


	// Validation error
	case UNKNOWN_ESHOP = 1301;
	case MISSING_LINK_OR_LANG = 1303;
	case INVALID_CATEGORY = 1304;
	case MISSING_LABEL = 1305;
	case INVALID_PAYMENT_METHOD = 1306;
	case NOT_ALLOWED_PAYMENT_METHOD = 1308;
	case INCORRECT_AMOUNT = 1309;
	case UNKNOWN_CURRENCY = 1310;
	case INVALID_BANK_ACCOUNT = 1311;
	case NOT_ALLOWED_RECURRING = 1316;
	case INVALID_RECURRING_METHOD = 1317;
	case INITIAL_PAYMENT_NOT_FOUND = 1318;

	// Internal errors
	case DATABASE_ERROR = 1200;
	case PAYMENT_BANK_ERROR = 1319;
	case UNEXPECTED_RESULT = 1399;
	case WRONG_QUERY = 1400;
	case UNEXPECTED_ERROR = 1500;

	// Recurring payment errors
	case REFUNDED_PAYMENT_CANCELED = 1401;

	public function getMessage() : string {
		return match ($this) {
			self::OK => 'OK',
			self::UNKNOWN_ERROR => 'Unknown error',
			self::LANGUAGE_ERROR => 'The specified language is not supported',
			self::PAYMENT_METHOD_ERROR => 'Method incorrectly specified',
			self::PAYMENT_LOAD_ERROR => 'Unable to load payment',
			self::PAYMENT_PRICE_ERROR => 'payment price is not supported',
			self::UNKNOWN_ESHOP => 'unknown e-shop',
			self::MISSING_LINK_OR_LANG => 'the link or language is missing',
			self::INVALID_CATEGORY => 'invalid category',
			self::MISSING_LABEL => 'product description is missing',
			self::INVALID_PAYMENT_METHOD => 'select the correct method',
			self::NOT_ALLOWED_PAYMENT_METHOD => 'the selected payment method is not allowed',
			self::INCORRECT_AMOUNT => 'Incorrect amount',
			self::UNKNOWN_CURRENCY => 'Unknown currency',
			self::INVALID_BANK_ACCOUNT => 'invalid e-shop bank account identifier',
			self::NOT_ALLOWED_RECURRING => 'e-shop does not allow recurring payments',
			self::INVALID_RECURRING_METHOD => 'invalid method - does not support recurring payments',
			self::INITIAL_PAYMENT_NOT_FOUND => 'initial payment not found',
			self::PAYMENT_BANK_ERROR => 'can not create a payment, a problem on the part of the bank',
			self::UNEXPECTED_RESULT => 'unexpected result from database',
			self::WRONG_QUERY => 'wrong query',
			self::UNEXPECTED_ERROR => 'unexpected error',
			self::REFUNDED_PAYMENT_CANCELED => 'the refunded payment is in the CANCELED state',
			self::DATABASE_ERROR => 'Database error',
			default => 'General error'
		};
	}

}