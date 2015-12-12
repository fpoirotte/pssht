<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Handlers\SERVICE;

use fpoirotte\Pssht\Messages\DISCONNECT;

/**
 * Handler for SSH_MSG_SERVICE_REQUEST messages.
 */
class REQUEST implements \fpoirotte\Pssht\Handlers\HandlerInterface
{
    /// User authentication request handler.
    protected $userAuthRequestHandler;

    /**
     * Construct a new handler for SSH_MSG_SERVICE_REQUEST messages.
     *
     *  \param fpoirotte::Pssht::Handlers::USERAUTH::REQUEST $methods
     *      User authentication request handler.
     */
    public function __construct(\fpoirotte\Pssht\Handlers\USERAUTH\REQUEST $methods)
    {
        $this->userAuthRequestHandler = $methods;
    }

    // SSH_MSG_SERVICE_REQUEST = 5
    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        $message    = \fpoirotte\Pssht\Messages\SERVICE\REQUEST::unserialize($decoder);
        $service    = $message->getServiceName();

        if ($service === 'ssh-userauth') {
            $response = new \fpoirotte\Pssht\Messages\SERVICE\ACCEPT($service);
            $transport->setHandler(
                \fpoirotte\Pssht\Messages\USERAUTH\REQUEST\Base::getMessageId(),
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
