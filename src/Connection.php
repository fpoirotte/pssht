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

class Connection implements \Clicky\Pssht\HandlerInterface
{
    protected $channels;

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

    public function allocateChannel(\Clicky\Pssht\MessageInterface $message)
    {
        for ($i = 0; isset($this->channels[$i]); ++$i) {
            // Do nothing.
        }
        $this->channels[$i] = $message->getSenderChannel();
        $this->handlers[$i] = array();
        return $i;
    }

    public function freeChannel($id)
    {
        if (!is_int($id)) {
            throw new \InvalidArgumentException();
        }

        unset($this->channels[$id]);
        unset($this->handlers[$id]);
        return $this;
    }

    public function getChannel($message)
    {
        if (is_int($message)) {
            return $this->channels[$message];
        }
        return $this->channels[$message->getChannel()];
    }

    public function setHandler(
        \Clicky\Pssht\MessageInterface $message,
        $type,
        \Clicky\Pssht\HandlerInterface $handler
    ) {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        $this->handlers[$message->getChannel()][$type] = $handler;
        return $this;
    }

    public function unsetHandler(
        \Clicky\Pssht\MessageInterface $message,
        $type,
        \Clicky\Pssht\HandlerInterface $handler
    ) {
        if (!is_int($type) || $type < 0 || $type > 255) {
            throw new \InvalidArgumentException();
        }

        if (isset($this->handlers[$message->getChannel()][$type]) &&
            $this->handlers[$message->getChannel()][$type] === $handler) {
            unset($this->handlers[$message->getChannel()][$type]);
        }
        return $this;
    }
}
