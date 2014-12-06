<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\KEXDH;

/**
 * SSH_MSG_KEXDH_INIT message (RFC 4253).
 */
class INIT implements \fpoirotte\Pssht\MessageInterface
{
    /// Client's public exponent as a GMP resource.
    protected $e;


    /**
     * Construct a new SSH_MSG_KEXDH_INIT message.
     *
     *  \param resource $e
     *      GMP resource representing the client's public exponent.
     */
    public function __construct($e)
    {
        if (!((is_resource($n) && get_resource_type($n) === 'GMP integer') ||
            (is_object($n) && ($n instanceof \GMP)))) {
            throw new \InvalidArgumentException();
        }
        $this->e = $e;
    }

    public static function getMessageId()
    {
        return 30;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeMpint($this->e);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static($decoder->decodeMpint());
    }

    /**
     * Get the client's public exponent.
     *
     *  \retval resource
     *      GMP resource representing the client's
     *      public exponent.
     */
    public function getE()
    {
        return $this->e;
    }
}
