<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Handlers\SERVICE;

use Clicky\Pssht\Messages\DISCONNECT;

/**
 * Handler for SSH_MSG_SERVICE_REQUEST messages.
 */
class REQUEST implements \Clicky\Pssht\HandlerInterface
{
    protected $userAuthRequestHandler;

    public function __construct(\Clicky\Pssht\Handlers\USERAUTH\REQUEST $methods)
    {
        $this->userAuthRequestHandler = $methods;
    }

    // SSH_MSG_SERVICE_REQUEST = 5
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $message    = \Clicky\Pssht\Messages\SERVICE\REQUEST::unserialize($decoder);
        $service    = $message->getServiceName();

        if ($service === 'ssh-userauth') {
            $response = new \Clicky\Pssht\Messages\SERVICE\ACCEPT($service);
            $transport->setHandler(
                \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base::getMessageId(),
                $this->userAuthRequestHandler
            );
        } else {
            $response = new DISCONNECT(
                DISCONNECT::SSH_DISCONNECT_SERVICE_NOT_AVAILABLE,
                'No such service'
            );
        }
        $transport->writeMessage($response);
        return true;
    }
}
