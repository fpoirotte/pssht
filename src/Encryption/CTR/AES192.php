<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption\CTR;

/**
 * AES cipher in CTR mode with a 192-bit key
 * (RECOMMENDED in RFC 4344).
 */
class AES192 extends \fpoirotte\Pssht\Encryption\CBC\AES192
{
}
