<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Authentication;

use Clicky\Pssht\AuthenticationInterface;

class None implements AuthenticationInterface
{
    public static function getName()
    {
        return 'none';
    }

    public function check(
        \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \Clicky\Pssht\Messages\USERAUTH\REQUEST\None)) {
            throw new \InvalidArgumentException();
        }

        return self::CHECK_OK;
    }

    public function authenticate(
        \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \Clicky\Pssht\Messages\USERAUTH\REQUEST\None)) {
            throw new \InvalidArgumentException();
        }

        return self::AUTH_REJECT;
    }
}
