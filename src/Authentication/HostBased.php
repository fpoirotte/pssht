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
 * Host based authentication.
 */
class HostBased implements AuthenticationInterface
{
    /// Store for the host keys.
    protected $store;

    /**
     * Construct a new host based authentication handler.
     *
     *  \param fpoirotte::Pssht::KeyStore $store
     *      Store containing the host keys to authorize.
     */
    public function __construct(\fpoirotte\Pssht\KeyStore $store)
    {
        $this->store = $store;
    }

    public static function getName()
    {
        return 'hostbased';
    }

    public function check(
        \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\HostBased)) {
            throw new \InvalidArgumentException();
        }

        return self::CHECK_OK;
    }

    public function authenticate(
        \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\HostBased)) {
            throw new \InvalidArgumentException();
        }

        $logging        = \Plop::getInstance();
        $reverse        = gethostbyaddr($transport->getAddress());
        $untrustedHost  = rtrim($message->getHostname(), '.');
        $algos          = \fpoirotte\Pssht\Algorithms::factory();
        $cls            = $algos->getClass('PublicKey', $message->getAlgorithm());

        if ($cls === null || !$this->store->exists($message->getUserName(), $message->getKey())) {
            $logging->info(
                'Rejected host based connection from %(ruser)s@%(rhost)s ' .
                '(%(ruser)s@%(reverse)s) to "%(luser)s" '.
                '(unsupported key)',
                array(
                    'ruser' => escape($message->getRemoteUser()),
                    'luser' => escape($message->getUserName()),
                    'rhost' => escape($untrustedHost),
                    'reverse' => $reverse,
                )
            );
            return self::AUTH_REMOVE;
        }

        $key        = $cls::loadPublic(base64_encode($message->getKey()));
        $encoder    = new \fpoirotte\Pssht\Wire\Encoder();
        $encoder->encodeString($context['DH']->getExchangeHash());
        $encoder->encodeBytes(chr(\fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Base::getMessageId()));
        $encoder->encodeString($message->getUserName());
        $encoder->encodeString($message->getServiceName());
        $encoder->encodeString(static::getName());
        $encoder->encodeString($message->getAlgorithm());
        $encoder->encodeString($message->getKey());
        $encoder->encodeString($message->getHostname());
        $encoder->encodeString($message->getRemoteUser());

        if (!$key->check($encoder->getBuffer()->get(0), $message->getSignature())) {
            $logging->warn(
                'Rejected host based connection from %(ruser)s@%(rhost)s ' .
                '(%(ruser)s@%(reverse)s) to "%(luser)s" (invalid signature)',
                array(
                    'ruser' => escape($message->getRemoteUser()),
                    'luser' => escape($message->getUserName()),
                    'rhost' => escape($untrustedHost),
                    'reverse' => $reverse,
                )
            );
            return self::AUTH_REJECT;
        }

        if ($reverse !== $untrustedHost) {
            $logging->warning(
                'Ignored reverse lookup mismatch for %(address)s (' .
                '"%(reverse)s" vs. "%(untrusted)s")',
                array(
                    'address' => $transport->getAddress(),
                    'reverse' => $reverse,
                    'untrusted' => escape($untrustedHost),
                )
            );
        }

        if ($message->getUserName() !== $message->getRemoteUser()) {
            $logging->warning(
                'Rejected host based connection from %(ruser)s@%(rhost)s ' .
                '(%(ruser)s@%(reverse)s): remote user does not match '.
                'local user (%(luser)s)',
                array(
                    'ruser' => escape($message->getRemoteUser()),
                    'luser' => escape($message->getUserName()),
                    'rhost' => escape($untrustedHost),
                    'reverse' => $reverse,
                )
            );
            return self::AUTH_REMOVE;
        }

        $logging->info(
            'Accepted host based connection ' .
            'from "%(ruser)s@%(rhost)s" (%(ruser)s@%(reverse)s) ' .
            'to "%(luser)s" (using "%(algorithm)s" algorithm)',
            array(
                'ruser' => escape($message->getRemoteUser()),
                'luser' => escape($message->getUserName()),
                'rhost' => escape($untrustedHost),
                'reverse' => $reverse,
                'algorithm' => escape($message->getAlgorithm()),
            )
        );
        return self::AUTH_ACCEPT;
    }
}
