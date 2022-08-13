<?php

namespace Heroyt\ComGate\Payment;

/**
 * @property string $value
 * @method static tryFrom(mixed $status)
 */
enum State : string
{

	case PENDING = 'PENDING';
	case AUTHORIZED = 'AUTHORIZED';
	case PAID = 'PAID';
	case CANCELLED = 'CANCELLED';

	// Apple-pay specific
	case FAILED = 'FAILED';
	case BANK_REJECT = 'BANK-REJECT';

}