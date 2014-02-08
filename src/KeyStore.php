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

class KeyStore
{
    protected $keys;

    public function __construct()
    {
        $this->keys = array();
    }

    protected function getIdentifier(PublicKeyInterface $key)
    {
        $encoder = new \Clicky\Pssht\Wire\Encoder();
        $key->serialize($encoder);
        return $encoder->getBuffer()->get(0);
    }

    public function add($user, PublicKeyInterface $key)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }

        $this->keys[$user][$this->getIdentifier($key)] = $key;
    }

    public function remove($user, PublicKeyInterface $key)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }

        unset($this->keys[$user][$this->getIdentifier($key)]);
    }

    public function get($user)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }

        if (!isset($this->keys[$user])) {
            return new \ArrayIterator();
        }

        return new \ArrayIterator($this->keys[$user]);
    }

    public function exists($user, $key)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }

        if ($key instanceof PublicKeyInterface) {
            $key = $this->getIdentifier($key);
        }
        if (!is_string($key)) {
            throw new \InvalidArgumentException();
        }

        return isset($this->keys[$user][$key]);
    }

    public function count($user)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }

        if (!isset($this->keys[$user])) {
            return 0;
        }

        return count($this->keys[$user]);
    }
}
