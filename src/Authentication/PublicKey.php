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

/**
 * Public key authentication.
 */
class PublicKey implements AuthenticationInterface
{
    /// Store for the public keys.
    protected $store;

    /**
     * Construct a new public key authentication handler.
     *
     *  \param Clicky::Pssht::KeyStore $store
     *      Store containing the public keys to authorize.
     */
    public function __construct(\Clicky\Pssht\KeyStore $store)
    {
        $this->store = $store;
    }

    public static function getName()
    {
        return 'publickey';
    }

    public function check(
        \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \Clicky\Pssht\Messages\USERAUTH\REQUEST\PublicKey)) {
            throw new \InvalidArgumentException();
        }

        if ($message->getSignature() !== null) {
            return self::CHECK_OK;
        }

        $algos = \Clicky\Pssht\Algorithms::factory();
        if ($algos->getClass('PublicKey', $message->getAlgorithm()) !== null &&
            $this->store->exists($message->getUserName(), $message->getKey())) {
            $response = new \Clicky\Pssht\Messages\USERAUTH\PK\OK(
                $message->getAlgorithm(),
                $message->getKey()
            );
            $transport->writeMessage($response);
            return self::CHECK_IGNORE;
        }
        return self::CHECK_REJECT;
    }

    public function authenticate(
        \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base $message,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        if (!($message instanceof \Clicky\Pssht\Messages\USERAUTH\REQUEST\PublicKey)) {
            throw new \InvalidArgumentException();
        }


        if ($message->getSignature() === null) {
            return self::AUTH_REJECT;
        }

        $logging    = \Plop::getInstance();
        $reverse    = gethostbyaddr($transport->getAddress());
        $algos      = \Clicky\Pssht\Algorithms::factory();
        $cls        = $algos->getClass('PublicKey', $message->getAlgorithm());
        if ($cls === null || !$this->store->exists($message->getUserName(), $message->getKey())) {
            $logging->info(
                'Rejected public key connection from remote host "%(reverse)s" ' .
                'to "%(luser)s" (unsupported key)',
                array(
                    'luser' => escape($message->getUserName()),
                    'reverse' => $reverse,
                )
            );
            return self::AUTH_REJECT;
        }

        $key        = $cls::loadPublic(base64_encode($message->getKey()));
        $encoder    = new \Clicky\Pssht\Wire\Encoder();
        $encoder->encodeString($context['DH']->getExchangeHash());
        $encoder->encodeBytes(chr(\Clicky\Pssht\Messages\USERAUTH\REQUEST\Base::getMessageId()));
        $encoder->encodeString($message->getUserName());
        $encoder->encodeString($message->getServiceName());
        $encoder->encodeString(static::getName());
        $encoder->encodeBoolean(true);
        $encoder->encodeString($message->getAlgorithm());
        $encoder->encodeString($message->getKey());

        if ($key->check($encoder->getBuffer()->get(0), $message->getSignature())) {
            $logging->info(
                'Accepted public key connection from remote host "%(reverse)s" ' .
                'to "%(luser)s" (using "%(algorithm)s" algorithm)',
                array(
                    'luser' => escape($message->getUserName()),
                    'reverse' => $reverse,
                    'algorithm' => escape($message->getAlgorithm()),
                )
            );
            return self::AUTH_ACCEPT;
        }

        $logging->info(
            'Rejected public key connection from remote host "%(reverse)s" ' .
            'to "%(luser)s" (invalid signature)',
            array(
                'luser' => escape($message->getUserName()),
                'reverse' => $reverse,
            )
        );
        return self::AUTH_REJECT;
    }
}
