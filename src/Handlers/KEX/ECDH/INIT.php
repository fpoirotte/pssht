<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Handlers\KEX\ECDH;

/**
 * Handler for SSH_MSG_KEX_ECDH_INIT messages.
 */
class INIT extends \fpoirotte\Pssht\Handlers\KEXDH\INIT
{
    protected function createResponse(
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context,
        $hostAlgo
    ) {
        $kexAlgo    = $context['kexAlgo'];
        $kexAlgo    = new $kexAlgo();
        $curveName  = str_replace('ecdh-sha2-', '', $kexAlgo::getName());
        $cls        = str_replace('nist', 'NIST', $curveName);
        $cls        = "\\fpoirotte\\Pssht\\Messages\\KEX\\ECDH\\INIT\\$cls";
        $message    = $cls::unserialize($decoder);
        $curve      = \fpoirotte\Pssht\ECC\Curve::getCurve($curveName);

        return new \fpoirotte\Pssht\Messages\KEX\ECDH\REPLY(
            $curve,
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
