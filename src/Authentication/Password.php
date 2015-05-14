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
 * Password authentication.
 */
class Password implements AuthenticationInterface
{
    /// Credentials of allowed users.
    protected $credentials;

    /**
     * Construct a new password authentication handler.
     *
     *  \param array $credentials
     *      Array with allowed users as keys
     *      and their respective passwords as values.
     */
    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public static function getName()
    {
        return 'password';
    }

    public function check(
        \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Password)) {
            throw new \InvalidArgumentException();
        }

        return self::CHECK_OK;
    }

    public function authenticate(
        \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Password)) {
            throw new \InvalidArgumentException();
        }

        $logging = \Plop\Plop::getInstance();
        $reverse = gethostbyaddr($transport->getAddress());

        if (isset($this->credentials[$message->getUserName()]) &&
            $message->getPassword() === $this->credentials[$message->getUserName()]) {
            $logging->info(
                'Accepted password based connection from remote host ' .
                '"%(reverse)s" (%(address)s) to "%(luser)s"',
                array(
                    'reverse' => $reverse,
                    'address' => $transport->getAddress(),
                    'luser' => escape($message->getUserName())
                )
            );
            return self::AUTH_ACCEPT;
        }

        $logging->info(
            'Rejected password based connection from remote host ' .
            '"%(reverse)s" (%(address)s) to "%(luser)s": ' .
            'invalid credentials',
            array(
                'reverse' => $reverse,
                'address' => $transport->getAddress(),
                'luser' => escape($message->getUserName())
            )
        );
        return self::AUTH_REJECT;
    }
}
