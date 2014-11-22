<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\SERVICE;

/**
 * Abstract SSH_MSG_SERVICE_* message (RFC 4253).
 */
abstract class Base implements \fpoirotte\Pssht\MessageInterface
{
    /// Name of the service to start after authentication.
    protected $service;

    /**
     * Construct a new SSH service request or acceptance message.
     *
     *  \param string $service
     *      Name of the service concerned by this request/acceptance
     *      message.
     */
    public function __construct($service)
    {
        if (!is_string($service)) {
            throw new \InvalidArgumentException();
        }

        $this->service = $service;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeString($this->service);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static($decoder->decodeString());
    }

    /**
     * Get the name of the service this message deals with.
     *
     *  \retval string
     *      Service name.
     */
    public function getServiceName()
    {
        return $this->service;
    }
}
