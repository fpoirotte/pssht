<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\USERAUTH\REQUEST;

use Clicky\Pssht\MessageInterface;

/**
 * Abstract SSH_MSG_USERAUTH_REQUEST message (RFC 4252).
 */
abstract class Base implements MessageInterface
{
    /// User being authenticated.
    protected $user;

    /// Service to start after authentication.
    protected $service;

    /// Authentication method.
    protected $method;

    /**
     * Construct a new user authentication request.
     *
     *  \param string $user
     *      User to authenticate as.
     *
     *  \param string $service
     *      Service to run after authentication.
     *
     *  \param string $method
     *      Authentication method to use.
     */
    public function __construct($user, $service, $method)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($service)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($method)) {
            throw new \InvalidArgumentException();
        }

        $this->user    = $user;
        $this->service = $service;
        $this->method  = $method;
    }

    public static function getMessageId()
    {
        return 50;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString($this->user);
        $encoder->encodeString($this->service);
        $encoder->encodeString($this->method);
        return $this;
    }

    /**
     * Unserialize the sub-message.
     *
     *  \param Clicky::Pssht::Wire::Decoder $decoder
     *      Decoder to use during unserialization.
     *
     *  \retval array
     *      Array of unserialized data forming
     *      the sub-message.
     *
     *  \note
     *      This method MUST be redefined by subclasses.
     *      The default implementation simply throws an exception.
     *
     *  @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected static function unserializeSub(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        throw new \RuntimeException();
    }

    final public static function unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        $reflector  = new \ReflectionClass(get_called_class());
        $args       = array_merge(
            array(
                $decoder->decodeString(),
                $decoder->decodeString(),
                $decoder->decodeString()
            ),
            static::unserializeSub($decoder)
        );
        return $reflector->newInstanceArgs($args);
    }

    /**
     * Return the name of the user requesting authentication.
     *
     *  \retval string
     *      User requesting authentication.
     */
    public function getUserName()
    {
        return $this->user;
    }

    /**
     * Return the name of the service to start
     * after authentication.
     *
     *  \retval string
     *      Service to start after authentication.
     */
    public function getServiceName()
    {
        return $this->service;
    }

    /**
     * Authentication method to use.
     *
     *  \retval string
     *      Authentication method to use.
     */
    public function getMethodName()
    {
        return $this->method;
    }
}
