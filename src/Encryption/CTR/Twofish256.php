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
 * Twofish cipher in CTR mode with a 256-bit key
 * (OPTIONAL in RFC 4344).
 */
class Twofish256 extends \fpoirotte\Pssht\Encryption\CBC\Twofish256
{
}
