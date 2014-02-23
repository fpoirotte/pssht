<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Encryption\CTR;

/**
 * Serpent cipher in CTR mode with a 192-bit key
 * (OPTIONAL in RFC 4344).
 */
class Serpent192 extends \Clicky\Pssht\Encryption\CBC\Serpent192
{
}
