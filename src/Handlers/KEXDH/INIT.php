<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Handlers\KEXDH;

class INIT implements \Clicky\Pssht\HandlerInterface
{
    // SSH_MSG_KEXDH_INIT = 30
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $message    = \Clicky\Pssht\Messages\KEXDH\INIT::unserialize($decoder);
        $kexAlgo    = $context['kexAlgo'];
        $kexAlgo    = new $kexAlgo();
        $response   = new \Clicky\Pssht\Messages\KEXDH\REPLY(
            $message,
            new \Clicky\Pssht\PublicKey\SSH\RSA(
                'file://' .
                dirname(dirname(dirname(__DIR__))) .
                '/tests/data/rsa2048'
            ),
            $transport->getEncryptor(),
            $transport->getDecryptor(),
            $kexAlgo,
            $context['kex']['server'],
            $context['kex']['client'],
            $context['identity']['server'],
            $context['identity']['client']
        );
        $transport->writeMessage($response);

        if (!isset($context['sessionIdentifier'])) {
            $context['sessionIdentifier'] = $response->getExchangeHash();
        }
        $context['DH'] = $response;
        return true;
    }
}
