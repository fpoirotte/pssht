<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\CHANNEL\REQUEST;

/**
 * Abstract SSH_MSG_CHANNEL_REQUEST message (RFC 4254).
 */
abstract class Base extends \fpoirotte\Pssht\Messages\CHANNEL\Base
{
    /// Message type.
    protected $type;

    /// Whether the sender of the message wants a reply or not.
    protected $wantReply;


    /**
     *  \copydetails fpoirotte::Pssht::Messages::CHANNEL::Base::__construct
     *
     *  \param string $type
     *      Message type.
     *
     *  \param bool $wantReply
     *      Indicates whether the sender of this message
     *      wants a reply (\b true) or not (\b false).
     */
    public function __construct($channel, $type, $wantReply)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException();
        }

        if (!is_bool($wantReply)) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($channel);
        $this->type         = $type;
        $this->wantReply    = $wantReply;
    }

    public static function getMessageId()
    {
        return 98;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeString($this->type);
        $encoder->encodeBoolean($this->wantReply);
        return $this;
    }

    /**
     * Unserialize the sub-message.
     *
     *  \param fpoirotte::Pssht::Wire::Decoder $decoder
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
    protected static function unserializeSub(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        throw new \RuntimeException();
    }

    final public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        $reflector  = new \ReflectionClass(get_called_class());
        $args       = array_merge(
            array(
                $decoder->decodeUint32(),   // channel
                $decoder->decodeString(),
                $decoder->decodeBoolean()
            ),
            static::unserializeSub($decoder)
        );
        return $reflector->newInstanceArgs($args);
    }

    /**
     * Get message type.
     *
     *  \retval string
     *      Message type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Flag indicating whether the sender expects a reply.
     *
     *  \retval bool
     *      \b true if the sender wants a reply,
     *      \b false otherwise.
     */
    public function wantsReply()
    {
        return $this->wantReply;
    }
}
