<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Handlers\USERAUTH;

use Clicky\Pssht\Messages\Disconnect;

class REQUEST implements \Clicky\Pssht\HandlerInterface
{
    protected $methods;
    protected $connection;

    public function __construct()
    {
        $this->method       = array();
        $this->connection   = null;
    }

    // SSH_MSG_USERAUTH_REQUEST = 50
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $message    = \Clicky\Pssht\Messages\USERAUTH\REQUEST::unserialize($decoder);
        $response   = new \Clicky\Pssht\Messages\USERAUTH\FAILURE(array(), false);
        if ($message->getUserName() === 'clicky' &&
            $message->getServiceName() === 'ssh-connection' &&
            $message->getMethodName() === 'none' &&
            $this->connection === null) {
                $response = new \Clicky\Pssht\Messages\USERAUTH\SUCCESS();
                $this->connection = new \Clicky\Pssht\Connection($transport, $message);
        }
        $transport->writeMessage($response);
        return true;
    }
}
