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

/**
 * Interface for an SSH message handler.
 */
interface HandlerInterface
{
    /**
     * Handle an SSH message.
     *
     *  \param int $msgType
     *      Message identifier.
     *
     *  \param Decoder $decoder
     *      Decoder for the message.
     *
     *  \param Transport $transport
     *      Transport layer the message was received from.
     *
     *  \param array $context
     *      Context for the SSH connection.
     */
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    );
}
