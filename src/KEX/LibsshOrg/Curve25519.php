<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\KEX\LibsshOrg;

/**
 * Abstract class for Elliptic Curve Diffie-Hellman
 * key exchange using Curve25519 and SHA-256.
 */
class Curve25519 implements \fpoirotte\Pssht\KEX\KEXInterface
{
    public static function addHandlers(\fpoirotte\Pssht\Transport $transport)
    {
        $transport->setHandler(
            \fpoirotte\Pssht\Messages\KEX\ECDH\INIT\Curve25519::getMessageId(),
            new \fpoirotte\Pssht\Handlers\KEX\ECDH\INIT\Curve25519()
        );
    }

    public static function getName()
    {
        return 'curve25519-sha256@libssh.org';
    }

    public function hash($data)
    {
        return hash('sha256', $data, true);
    }

    public static function isAvailable()
    {
        if (!function_exists('hash_algos') || !function_exists('hash')) {
            return false;
        }
        return in_array('sha256', hash_algos(), true);
    }
}
