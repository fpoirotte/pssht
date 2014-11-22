<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\MAC\SHA1;

/**
 * MAC generation using a truncated SHA1 hash.
 */
class Len96 extends \fpoirotte\Pssht\MAC\Base96
{
    public static function getBaseClass()
    {
        return '\\fpoirotte\\Pssht\\MAC\\SHA1';
    }
}
