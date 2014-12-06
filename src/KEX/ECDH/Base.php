<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\KEX\ECDH;

/**
 * Abstract class for Elliptic Curve Diffie-Hellman
 * key exchange as defined in RFC 5656.
 */
abstract class Base implements
 \fpoirotte\Pssht\KEXInterface,
 \fpoirotte\Pssht\AvailabilityInterface,
 \fpoirotte\Pssht\KEX\ECDH\BaseInterface
{
    public static function addHandlers(\fpoirotte\Pssht\Transport $transport)
    {
        $transport->setHandler(
            \fpoirotte\Pssht\Messages\KEX\ECDH\INIT::getMessageId(),
            new \fpoirotte\Pssht\Handlers\KEX\ECDH\INIT()
        );
    }

    public function hash($data)
    {
        return hash(static::getHashName(), $data, true);
    }

    public static function isAvailable()
    {
        if (!function_exists('hash_algos') || !function_exists('hash')) {
            return false;
        }
        return in_array(static::getHashName(), hash_algos(), true);
    }
}
