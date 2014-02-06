<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Handlers;

class InitialState implements \Clicky\Pssht\HandlerInterface
{
    // Initial state
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $ident = $decoder->getBuffer()->get("\r\n");
        if ($ident === null) {
            throw new \RuntimeException();
        }

        $context['identity']['client'] = (string) substr($ident, 0, -2);

        if (strncmp($ident, 'SSH-2.0-', 8) !== 0) {
            throw new \Clicky\Pssht\Messages\DISCONNECT();
        }

        $random = new \Clicky\Pssht\Random\OpenSSL();
        $kex    = new \Clicky\Pssht\Messages\KEXINIT($random);
        $context['kex']['server'] = $kex;
        $transport->writeMessage($kex);
        return true;
    }
}
