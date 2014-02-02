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

class SSHUserAuth
{
    protected $transport;
    protected $connection;

    public function __construct(Client $transport)
    {
        $this->transport    = $transport;
        $this->connection   = null;
    }

    public static function getName()
    {
        return 'ssh-userauth';
    }

    public function getTransport()
    {
        return $this->transport;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function writeMessage(\Clicky\Pssht\MessageInterface $message)
    {
        return $this->transport->writeMessage($message);
    }

    public function handleMessage($msgType, Decoder $decoder, $remaining)
    {
        switch ($msgType) {
            case \Clicky\Pssht\Messages\USERAUTH\REQUEST::getMessageId():
                $message = \Clicky\Pssht\Messages\USERAUTH\REQUEST::unserialize($decoder);
                $response = new \Clicky\Pssht\Messages\USERAUTH\FAILURE(array(), false);
                if ($message->getUserName() === 'clicky' &&
                    $message->getServiceName() === 'ssh-connection' &&
                    $message->getMethodName() === 'none' &&
                    $this->connection === null) {
                        $response = new \Clicky\Pssht\Messages\USERAUTH\SUCCESS();
                        $this->connection = new \Clicky\Pssht\Connection($this, $message);
                }
                $this->transport->writeMessage($response);
                break;

            default:
                if ($this->connection !== null) {
                    return $this->connection->handleMessage($msgType, $decoder, $remaining);
                }
                throw new \RuntimeException();
        }
        return true;
    }
}
