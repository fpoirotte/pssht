<?php
/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Application;

use \fpoirotte\Pssht\Messages\DISCONNECT;

class TestService implements \fpoirotte\Pssht\Handlers\HandlerInterface
{
    public function __construct(
        \fpoirotte\Pssht\Transport $transport,
        \fpoirotte\Pssht\Connection $connection,
        \fpoirotte\Pssht\Messages\MessageInterface $message
    ) {
        if (!($message instanceof \fpoirotte\Pssht\Messages\CHANNEL\REQUEST\Exec)) {
            throw new DISCONNECT(
                DISCONNECT::SSH_DISCONNECT_SERVICE_NOT_AVAILABLE,
                'No shell for you!'
            );
        }

        $response   = new \fpoirotte\Pssht\Messages\CHANNEL\DATA(
            $message->getChannel(),
            'Your number: ' . $message->getCommand() . PHP_EOL
        );
        $transport->writeMessage($response);

        $response   = new \fpoirotte\Pssht\Messages\CHANNEL\EOF(
            $message->getChannel()
        );
        $transport->writeMessage($response);

        /// @FIXME: We shouldn't need to pass values
        //          for the "type" & "want-replay" fields.
        $response   = new \fpoirotte\Pssht\Messages\CHANNEL\REQUEST\ExitStatus(
            $message->getChannel(),
            "exit-status",
            false,
            (int) $message->getCommand()
        );
        $transport->writeMessage($response);

        /// @FIXME: We shouldn't need to pass values
        //          for the "type" & "want-replay" fields.
        $response   = new \fpoirotte\Pssht\Messages\CHANNEL\REQUEST\OpensshCom\Eow(
            $message->getChannel(),
            "eow@openssh.com",
            false
        );
        $transport->writeMessage($response);

        $response   = new \fpoirotte\Pssht\Messages\CHANNEL\CLOSE(
            $message->getChannel()
        );
        $transport->writeMessage($response);
     }

    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        // Unused, but still required by the interface.
    }
}
