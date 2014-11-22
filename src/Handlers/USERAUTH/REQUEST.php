<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Handlers\USERAUTH;

use fpoirotte\Pssht\Messages\Disconnect;
use fpoirotte\Pssht\AuthenticationInterface;

/**
 * Handler for SSH_MSG_USERAUTH_REQUEST messages.
 */
class REQUEST implements \fpoirotte\Pssht\HandlerInterface
{
    /// Allowed authentication methods.
    protected $methods;

    /// Connection layer.
    protected $connection;

    /**
     * Construct a new handler for SSH_MSG_USERAUTH_REQUEST messages.
     *
     *  \param array $methods
     *      Allowed authentication methods.
     */
    public function __construct(array $methods)
    {
        $method         = new \fpoirotte\Pssht\Authentication\None();
        $realMethods    = array($method->getName() => $method);
        foreach ($methods as $method) {
            if (!($method instanceof \fpoirotte\Pssht\AuthenticationInterface)) {
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
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        if ($this->connection !== null) {
            // Silently ignore subsequent authentication requests
            // after a successful authentication took place.
            return true;
        }

        $encoder    = new \fpoirotte\Pssht\Wire\Encoder();
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
                $response = new \fpoirotte\Pssht\Messages\USERAUTH\BANNER($context['banner']);
                $transport->writeMessage($response);
            }
        }

        if (!isset($context['authMethods'][$method])) {
            return $this->failure($transport, $context);
        }

        $messagesCls = array(
            'none' =>
                '\\fpoirotte\\Pssht\\Messages\\USERAUTH\\REQUEST\\None',
            'hostbased' =>
                '\\fpoirotte\\Pssht\\Messages\\USERAUTH\\REQUEST\\HostBased',
            'password' =>
                '\\fpoirotte\\Pssht\\Messages\\USERAUTH\\REQUEST\\Password',
            'publickey' =>
                '\\fpoirotte\\Pssht\\Messages\\USERAUTH\\REQUEST\\PublicKey',
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
        $response = new \fpoirotte\Pssht\Messages\USERAUTH\SUCCESS();
        $this->connection = new \fpoirotte\Pssht\Connection($transport);
        $transport->writeMessage($response);

        $compressor = $transport->getCompressor();
        if ($compressor instanceof \fpoirotte\Pssht\DelayedCompressionInterface) {
            $compressor->setAuthenticated();
        }
        $uncompressor = $transport->getUncompressor();
        if ($uncompressor instanceof \fpoirotte\Pssht\DelayedCompressionInterface) {
            $uncompressor->setAuthenticated();
        }

        return true;
    }

    /**
     * Report an authentication failure.
     *
     *  \param fpoirotte::Pssht::Transport $transport
     *      Transport layer used to report the failure.
     *
     *  \param array &$context
     *      SSH session context (containing authentication methods
     *      that may continue).
     *
     *  \param bool $partial
     *      (optional) Indicates whether the request ended with
     *      a partial success (\b true) or not (\b false).
     *      If omitted, \b false is implied.
     *
     *  \retval true
     *      This method always returns true.
     */
    protected function failure(
        \fpoirotte\Pssht\Transport $transport,
        array &$context,
        $partial = false
    ) {
        if (!is_bool($partial)) {
            throw new \InvalidArgumentException();
        }

        $remaining  = $context['authMethods'];
        unset($remaining['none']);
        $remaining  = array_keys($remaining);
        $response   = new \fpoirotte\Pssht\Messages\USERAUTH\FAILURE($remaining, $partial);
        $transport->writeMessage($response);
        return true;
    }
}
