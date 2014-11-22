<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht;

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
     *  \param fpoirotte::Pssht::Wire::Decoder $decoder
     *      Decoder for the message.
     *
     *  \param fpoirotte::Pssht::Transport $transport
     *      Transport layer the message was received from.
     *
     *  \param array $context
     *      Context for the SSH connection.
     */
    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    );
}
