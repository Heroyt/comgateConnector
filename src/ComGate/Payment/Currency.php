<?php

namespace Heroyt\ComGate\Payment;

/**
 * @property string $value
 * @method static tryFrom(mixed $curr)
 */
enum Currency : string
{

	case CZK = 'CZK';
	case EUR = 'EUR';
	case PLN = 'PLN';
	case USD = 'USD';
	case HUF = 'HUF';
	case GBP = 'GBP';
	case RON = 'RON';
	case HRK = 'HRK';
	case NOK = 'NOK';
	case SEK = 'SEK';

}