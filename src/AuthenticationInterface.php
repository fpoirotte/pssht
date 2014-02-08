<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht;

interface AuthenticationInterface
{
    const CHECK_OK      = 1;
    const CHECK_REJECT  = 2;
    const CHECK_IGNORE  = 3;

    const AUTH_ACCEPT   = 1;
    const AUTH_REJECT   = 2;
    const AUTH_REMOVE   = 3;

    public static function getName();

    public function check(
        \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \Clicky\Pssht\Transport $transport,
        array &$context
    );

    public function authenticate(
        \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \Clicky\Pssht\Transport $transport,
        array &$context
    );
}
