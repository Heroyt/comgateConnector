<?php

namespace Heroyt\ComGate\Payment;

/**
 * @property string $value
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