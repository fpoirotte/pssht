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

class REQUEST implements \Clicky\Pssht\HandlerInterface
{
    protected $userAuthRequestHandler;

    public function __construct()
    {
        /// @FIXME: use DI for this
        $store  = new \Clicky\Pssht\KeyStore();
        $loader = new \Clicky\Pssht\KeyStoreLoader\File($store);
        $loader->load('clicky', '/home/clicky/.ssh/authorized_keys');

        $store2 = new \Clicky\Pssht\KeyStore();
        $loader = new \Clicky\Pssht\KeyStoreLoader\File($store2);
        $loader->load('clicky2', '/etc/ssh/ssh_host_rsa_key.pub');
        $loader->load('clicky2', '/etc/ssh/ssh_host_dsa_key.pub');

        $this->userAuthRequestHandler = new \Clicky\Pssht\Handlers\USERAUTH\REQUEST(
            array(
                new \Clicky\Pssht\Authentication\None(),
                new \Clicky\Pssht\Authentication\Password(
                    array(
                        'clicky' => 'test',
                    )
                ),
                new \Clicky\Pssht\Authentication\PublicKey($store),
                new \Clicky\Pssht\Authentication\HostBased($store2),
            )
        );
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
