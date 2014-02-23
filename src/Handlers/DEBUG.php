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

/**
 * Handler for SSH_MSG_DEBUG messages.
 */
class DEBUG implements \Clicky\Pssht\HandlerInterface
{
    // SSH_MSG_DEBUG = 4
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $message = \Clicky\Pssht\Messages\DEBUG::unserialize($decoder);
        if ($message->mustAlwaysDisplay()) {
            echo escape($message->getMessage()) . PHP_EOL;
        }
        return true;
    }
}
