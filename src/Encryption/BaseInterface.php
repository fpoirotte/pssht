<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption;

/**
 * Base interface for encryption/decryption ciphers.
 */
interface BaseInterface
{
    /**
     * Get the encryption/decryption mode.
     *
     *  \retval string
     *      Name of the cipher encryption/decryption mode to use.
     *
     *  \warning
     *      The return value of this method is the name of the cipher mode,
     *      ie. the value of one of the "MCRYPT_MODE_*" constants.
     */
    public static function getMode();

    /**
     * Get the name of the algorithm to use.
     *
     *  \retval string
     *      Name of the mcrypt constant representing the algorithm
     *      to use for encryption/decryption.
     *
     *  \warning
     *      The return value of this method is the name of the constant
     *      for the algorithm to use (eg. "MCRYPT_TRIPLEDES"),
     *      not its value.
     */
    public static function getAlgorithm();
}
