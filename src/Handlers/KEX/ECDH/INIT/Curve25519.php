<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Handlers\KEX\ECDH\INIT;

/**
 * Handler for SSH_MSG_KEX_ECDH_INIT messages.
 */
class Curve25519 extends \fpoirotte\Pssht\Handlers\KEXDH\INIT
{
    protected function createResponse(
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context,
        $hostAlgo
    ) {
        $kexAlgo    = $context['kexAlgo'];
        $kexAlgo    = new $kexAlgo();
        $message    = \fpoirotte\Pssht\Messages\KEX\ECDH\INIT\Curve25519::unserialize($decoder);

        return new \fpoirotte\Pssht\Messages\KEX\ECDH\REPLY\Curve25519(
            $message,
            $context['serverKeys'][$hostAlgo],
            $transport->getEncryptor(),
            $kexAlgo,
            $context['kex']['server'],
            $context['kex']['client'],
            $context['identity']['server'],
            $context['identity']['client']
        );
    }
}
