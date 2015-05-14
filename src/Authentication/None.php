<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Authentication;

use fpoirotte\Pssht\AuthenticationInterface;

/**
 * Anonymous authentication.
 *
 * For security reasons, this class rejects all authentication requests
 * and is only used to list supported authentication methods.
 */
class None implements AuthenticationInterface
{
    public static function getName()
    {
        return 'none';
    }

    public function check(
        \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\None)) {
            throw new \InvalidArgumentException();
        }

        return self::CHECK_OK;
    }

    public function authenticate(
        \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\None)) {
            throw new \InvalidArgumentException();
        }

        $logging    = \Plop\Plop::getInstance();
        $reverse    = gethostbyaddr($transport->getAddress());
        $logging->info(
            'Rejected anonymous connection from remote host ' .
            '"%(reverse)s" (%(address)s) to "%(luser)s": ' .
            'anonymous login is not permitted',
            array(
                'luser' => escape($message->getUserName()),
                'reverse' => $reverse,
                'address' => $transport->getAddress(),
            )
        );
        return self::AUTH_REJECT;
    }
}
