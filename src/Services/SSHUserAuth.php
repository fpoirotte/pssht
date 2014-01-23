<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Services;

use Clicky\Pssht\Client;
use Clicky\Pssht\Wire\Decoder;

class   SSHUserAuth
{
    protected $_transport;
    protected $_connection;

    public function __construct(Client $transport)
    {
        $this->_transport   = $transport;
        $this->_connection  = NULL;
    }

    static public function getName()
    {
        return 'ssh-userauth';
    }

    public function getTransport()
    {
        return $this->_transport;
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    public function writeMessage(\Clicky\Pssht\MessageInterface $message)
    {
        return $this->_transport->writeMessage($message);
    }

    public function handleMessage($msgType, Decoder $decoder, $remaining)
    {
        switch ($msgType) {
            case \Clicky\Pssht\Messages\USERAUTH_REQUEST::MESSAGE_ID:
                $message = \Clicky\Pssht\Messages\USERAUTH_REQUEST::unserialize($decoder);
                $response = new \Clicky\Pssht\Messages\USERAUTH_FAILURE(array(), FALSE);
                if ($message->getUserName() === 'clicky' &&
                    $message->getServiceName() === 'ssh-connection' &&
                    $message->getMethodName() === 'none' &&
                    $this->_connection === NULL) {
                        $response = new \Clicky\Pssht\Messages\USERAUTH_SUCCESS();
                        $this->_connection = new \Clicky\Pssht\Connection($this, $message);
                }
                $this->_transport->writeMessage($response);
                break;

            default:
                if ($this->_connection !== NULL) {
                    return $this->_connection->handleMessage($msgType, $decoder, $remaining);
                }
                throw new \RuntimeException();
        }
        return TRUE;
    }
}

