<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht;

use fpoirotte\Pssht\Messages\USERAUTH\REQUEST;
use fpoirotte\Pssht\Wire\Encoder;
use fpoirotte\Pssht\Wire\Decoder;

/**
 * Connection layer for the SSH protocol (RFC 4254).
 */
class Connection implements \fpoirotte\Pssht\Handlers\HandlerInterface
{
    /// Opened SSH channels.
    protected $channels;

    /**
     * Construct a new SSH connection layer.
     *
     *  \param fpoirotte::Pssht::Transport $transport
     *      SSH transport layer.
     */
    public function __construct(
        \fpoirotte\Pssht\Transport $transport
    ) {
        $this->channels     = array();

        $transport->setHandler(
            // 90
            \fpoirotte\Pssht\Messages\CHANNEL\OPEN::getMessageId(),
            new \fpoirotte\Pssht\Handlers\CHANNEL\OPEN($this)
        )->setHandler(
            // 97
            \fpoirotte\Pssht\Messages\CHANNEL\CLOSE::getMessageId(),
            new \fpoirotte\Pssht\Handlers\CHANNEL\CLOSE($this)
        )->setHandler(
            // 98
            \fpoirotte\Pssht\Messages\CHANNEL\REQUEST\Base::getMessageId(),
            new \fpoirotte\Pssht\Handlers\CHANNEL\REQUEST($this)
        );

        foreach (array_merge(range(91, 96), array(99, 100)) as $msgId) {
            $transport->setHandler($msgId, $this);
        }
    }

    public function handle(
        $msgType,
        \fpoirotte\Pssht\Wire\Decoder $decoder,
        \fpoirotte\Pssht\Transport $transport,
        array &$context
    ) {
        $localChannel   = $decoder->decodeUint32();
        $encoder        = new \fpoirotte\Pssht\Wire\Encoder();
        $encoder->encodeUint32($localChannel);
        $decoder->getBuffer()->unget($encoder->getBuffer()->get(0));

        if (isset($this->handlers[$localChannel][$msgType])) {
            $handler = $this->handlers[$localChannel][$msgType];
            $logging = \Plop\Plop::getInstance();
            $logging->debug(
                'Calling %(handler)s for channel #%(channel)d ' .
                'with message type #%(msgType)d',
                array(
                    'handler' => get_class($handler) . '::handle',
                    'channel' => $localChannel,
                    'msgType' => $msgType,
                )
            );
            return $handler->handle($msgType, $decoder, $transport, $context);
        }
        return true;
    }

    /**
     * Allocate a new communication channel.
     *
     *  \param fpoirotte::Pssht::Messages::CHANNEL::OPEN $message
     *      Original message requesting channel allocation.
     *
     *  \return int
     *      Newly allocated channel's identifier.
     */
    public function allocateChannel(\fpoirotte\Pssht\Messages\CHANNEL\OPEN $message)
    {
        for ($i = 0; isset($this->channels[$i]); ++$i) {
            // Do nothing.
        }
        $this->channels[$i] = $message->getChannel();
        $this->handlers[$i] = array();
        return $i;
    }

    /**
     * Free a channel allocation.
     *
     *  \param int $id
     *      Channel identifier.
     *
     *  \retval Connection
     *      Returns this connection.
     */
    public function freeChannel($id)
    {
        if (!is_int($id)) {
            throw new \InvalidArgumentException();
        }

        unset($this->channels[$id]);
        unset($this->handlers[$id]);
        return $this;
    }

    /**
     * Retrieve the channel associated with a message.
     *
     *  \param int|fpoirotte::Pssht::Messages::CHANNEL::REQUEST::Base $message
     *      Either a message or the message's channel identifier.
     *
     *  \retval int
     *      Remote channel associated with the message.
     */
    public function getChannel($message)
    {
        if (is_int($message)) {
            return $this->channels[$message];
        }
        return $this->channels[$message->getChannel()];
    }

    /**
     * Register a handler.
     *
     *  \param int|fpoirotte::Pssht::Messages::CHANNEL::REQUEST::Base $message
     *      Either a message or the message's channel identifier.
     *
     *  \param int $type
     *      Message type.
     *
     *  \param fpoirotte::Pssht::Handlers::HandlerInterface $handler
     *      Handler to associate with the message.
     */
    public function setHandler(
        $message,
        $type,
        \fpoirotte\Pssht\Handlers\HandlerInterface $handler
    ) {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        if (!is_int($message)) {
            if (!($message instanceof \fpoirotte\Pssht\Messages\CHANNEL\REQUEST\Base)) {
                throw new \InvalidArgumentException();
            }
            $message = $message->getChannel();
        }

        $this->handlers[$message][$type] = $handler;
        return $this;
    }

    /**
     * Unregister a handler.
     *
     *  \param int|fpoirotte::Pssht::Messages::CHANNEL::REQUEST::Base $message
     *      Either a message or the message's channel identifier.
     *
     *  \param int $type
     *      Message type.
     *
     *  \param fpoirotte::Pssht::Handlers::HandlerInterface $handler
     *      Handler to unregister.
     */
    public function unsetHandler(
        $message,
        $type,
        \fpoirotte\Pssht\Handlers\HandlerInterface $handler
    ) {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        if (!is_int($message)) {
            if (!($message instanceof \fpoirotte\Pssht\Messages\CHANNEL\REQUEST\Base)) {
                throw new \InvalidArgumentException();
            }
            $message = $message->getChannel();
        }

        if (isset($this->handlers[$message][$type]) &&
            $this->handlers[$message][$type] === $handler) {
            unset($this->handlers[$message][$type]);
        }
        return $this;
    }
}
