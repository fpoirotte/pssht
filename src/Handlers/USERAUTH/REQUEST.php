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
use Clicky\Pssht\AuthenticationInterface;

class REQUEST implements \Clicky\Pssht\HandlerInterface
{
    protected $methods;
    protected $connection;

    public function __construct(array $methods)
    {
        $method         = new \Clicky\Pssht\Authentication\None();
        $realMethods    = array($method->getName() => $method);
        foreach ($methods as $method) {
            if (!($method instanceof \Clicky\Pssht\AuthenticationInterface)) {
                throw new \InvalidArgumentException();
            }
            $realMethods[$method->getName()] = $method;
        }

        $this->methods      = $realMethods;
        $this->connection   = null;
    }

    // SSH_MSG_USERAUTH_REQUEST = 50
    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        if ($this->connection !== null) {
            // Silently ignore subsequent authentication requests
            // after a successful authentication took place.
            return true;
        }

        $encoder    = new \Clicky\Pssht\Wire\Encoder();
        $user       = $decoder->decodeString();
        $service    = $decoder->decodeString();
        $method     = $decoder->decodeString();

        $encoder->encodeString($user);
        $encoder->encodeString($service);
        $encoder->encodeString($method);
        $decoder->getBuffer()->unget($encoder->getBuffer()->get(0));

        if (!isset($context['authMethods'])) {
            $context['authMethods']     = $this->methods;
        }

        if (!isset($context['banner'])) {
            $context['banner'] = (string) $transport->getBanner();
            if ($context['banner'] !== '') {
                $response = new \Clicky\Pssht\Messages\USERAUTH\BANNER($context['banner']);
                $transport->writeMessage($response);
            }
        }

        if (!isset($context['authMethods'][$method])) {
            return $this->failure($transport, $context);
        }

        $messagesCls = array(
            'none' =>
                '\\Clicky\\Pssht\\Messages\\USERAUTH\\REQUEST\\None',
            'hostbased' =>
                '\\Clicky\\Pssht\\Messages\\USERAUTH\\REQUEST\\HostBased',
            'password' =>
                '\\Clicky\\Pssht\\Messages\\USERAUTH\\REQUEST\\Password',
            'publickey' =>
                '\\Clicky\\Pssht\\Messages\\USERAUTH\\REQUEST\\PublicKey',
        );
        $methodObj  = $context['authMethods'][$method];
        $message    = $messagesCls[$method]::unserialize($decoder);

        switch ($methodObj->check($message, $transport, $context)) {
            case AuthenticationInterface::CHECK_IGNORE:
                return true;

            case AuthenticationInterface::CHECK_REJECT:
                return $this->failure($transport, $context);

            case AuthenticationInterface::CHECK_OK:
                break;

            default:
                throw new \RuntimeException();
        }

        switch ($methodObj->authenticate($message, $transport, $context)) {
            case AuthenticationInterface::AUTH_REMOVE:
                unset($context['authMethods'][$method]);
                // Do not break.

            case AuthenticationInterface::AUTH_REJECT:
                return $this->failure($transport, $context);

            case AuthenticationInterface::AUTH_ACCEPT:
                break;

            default:
                throw new \RuntimeException();
        }

        unset($context['authMethods'][$method]);
        $response = new \Clicky\Pssht\Messages\USERAUTH\SUCCESS();
        $this->connection = new \Clicky\Pssht\Connection($transport);
        $transport->writeMessage($response);

        $compressor = $transport->getCompressor();
        if ($compressor instanceof \Clicky\Pssht\DelayedCompressionInterface) {
            $compressor->setAuthenticated();
        }
        $uncompressor = $transport->getUncompressor();
        if ($uncompressor instanceof \Clicky\Pssht\DelayedCompressionInterface) {
            $uncompressor->setAuthenticated();
        }

        return true;
    }

    protected function failure(
        \Clicky\Pssht\Transport $transport,
        array &$context,
        $partial = false
    ) {
        if (!is_bool($partial)) {
            throw new \InvalidArgumentException();
        }

        $remaining  = $context['authMethods'];
        unset($remaining['none']);
        $remaining  = array_keys($remaining);
        $response   = new \Clicky\Pssht\Messages\USERAUTH\FAILURE($remaining, $partial);
        $transport->writeMessage($response);
        return true;
    }
}
