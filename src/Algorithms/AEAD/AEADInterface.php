<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Algorithms\AEAD;

/**
 * Interface for Authenticated Encryption with Additional Data.
 */
interface AEADInterface extends \fpoirotte\Pssht\Encryption\EncryptionInterface
{
    /**
     * Get the size of an Authentication Tag
     * generated with this algorithm.
     *
     *  \retval int
     *      AT size in bytes.
     */
    public static function getSize();
}
