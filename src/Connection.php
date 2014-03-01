<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht;

use Clicky\Pssht\Messages\USERAUTH\REQUEST;
use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

/**
 * Connection layer for the SSH protocol (RFC 4254).
 */
class Connection implements \Clicky\Pssht\HandlerInterface
{
    /// Opened SSH channels.
    protected $channels;

    /**
     * Construct a new SSH connection layer.
     *
     *  \param Clicky::Pssht::Transport $transport
     *      SSH transport layer.
     */
    public function __construct(
        \Clicky\Pssht\Transport $transport
    ) {
        $this->channels     = array();

        $transport->setHandler(
            // 90
            \Clicky\Pssht\Messages\CHANNEL\OPEN::getMessageId(),
            new \Clicky\Pssht\Handlers\CHANNEL\OPEN($this)
        )->setHandler(
            // 97
            \Clicky\Pssht\Messages\CHANNEL\CLOSE::getMessageId(),
            new \Clicky\Pssht\Handlers\CHANNEL\CLOSE($this)
        )->setHandler(
            // 98
            \Clicky\Pssht\Messages\CHANNEL\REQUEST\Base::getMessageId(),
            new \Clicky\Pssht\Handlers\CHANNEL\REQUEST($this)
        );

        foreach (array_merge(range(91, 96), array(99, 100)) as $msgId) {
            $transport->setHandler($msgId, $this);
        }
    }

    public function handle(
        $msgType,
        \Clicky\Pssht\Wire\Decoder $decoder,
        \Clicky\Pssht\Transport $transport,
        array &$context
    ) {
        $localChannel   = $decoder->decodeUint32();
        $encoder        = new \Clicky\Pssht\Wire\Encoder();
        $encoder->encodeUint32($localChannel);
        $decoder->getBuffer()->unget($encoder->getBuffer()->get(0));

        if (isset($this->handlers[$localChannel][$msgType])) {
            $handler = $this->handlers[$localChannel][$msgType];
            $logging = \Plop::getInstance();
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
     *  \param Clicky::Pssht::Messages::CHANNEL::OPEN $message
     *      Original message requesting channel allocation.
     *
     *  \return int
     *      Newly allocated channel's identifier.
     */
    public function allocateChannel(\Clicky\Pssht\Messages\CHANNEL\OPEN $message)
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
     *  \param int|Clicky::Pssht::Messages::CHANNEL::REQUEST::Base $message
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
     *  \param int|Clicky::Pssht::Messages::CHANNEL::REQUEST::Base $message
     *      Either a message or the message's channel identifier.
     *
     *  \param int $type
     *      Message type.
     *
     *  \param Clicky::Pssht::HandlerInterface $handler
     *      Handler to associate with the message.
     */
    public function setHandler(
        $message,
        $type,
        \Clicky\Pssht\HandlerInterface $handler
    ) {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        if (!is_int($message)) {
            if (!($message instanceof \Clicky\Pssht\Messages\CHANNEL\REQUEST\Base)) {
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
     *  \param int|Clicky::Pssht::Messages::CHANNEL::REQUEST::Base $message
     *      Either a message or the message's channel identifier.
     *
     *  \param int $type
     *      Message type.
     *
     *  \param Clicky::Pssht::HandlerInterface $handler
     *      Handler to unregister.
     */
    public function unsetHandler(
        $message,
        $type,
        \Clicky\Pssht\HandlerInterface $handler
    ) {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        if (!is_int($message)) {
            if (!($message instanceof \Clicky\Pssht\Messages\CHANNEL\REQUEST\Base)) {
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
