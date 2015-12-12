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

/**
 * Provides storage for public/private keys.
 */
class KeyStore
{
    /// Public/private keys currently stored.
    protected $keys;

    /**
     * Construct a new store.
     */
    public function __construct()
    {
        $this->keys = array();
    }

    /**
     * Return the identifier for a key.
     *
     *  \param fpoirotte::Pssht::Key::KeyInterface $key
     *      Public or private key.
     *
     *  \retval string
     *      SSH identifier for the key.
     */
    protected function getIdentifier(\fpoirotte\Pssht\Key\KeyInterface $key)
    {
        $encoder = new \fpoirotte\Pssht\Wire\Encoder();
        $key->serialize($encoder);
        return $encoder->getBuffer()->get(0);
    }

    /**
     * Add a new key in the store.
     *
     *  \param string $user
     *      User the key belongs to.
     *
     *  \param fpoirotte::Pssht::Key::KeyInterface $key
     *      Public/private key to add.
     */
    public function add($user, \fpoirotte\Pssht\Key\KeyInterface $key)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }

        $this->keys[$user][$this->getIdentifier($key)] = $key;
    }

    /**
     * Remove a key from the store.
     *
     *  \param string $user
     *      User the key belongs to.
     *
     *  \param fpoirotte::Pssht::Key::KeyInterface $key
     *      Public/private key to remove.
     */
    public function remove($user, \fpoirotte\Pssht\Key\KeyInterface $key)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }

        unset($this->keys[$user][$this->getIdentifier($key)]);
    }

    /**
     * Retrieve a list of the keys currently
     * stored for the given user.
     *
     *  \param string $user
     *      User whose keys should be retrieved.
     *
     *  \retval array
     *      Public/private keys for the given user.
     */
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

    /**
     * Test whether a given key as been registered
     * for a specific user.
     *
     *  \param string $user
     *      User for which the key is tested.
     *
     *  \param string|fpoirotte::Pssht::Key::KeyInterface $key
     *      Key to test.
     *
     *  \retval bool
     *      \c true if the given key has been registered
     *      for the given user, \c false otherwise.
     */
    public function exists($user, $key)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }

        if ($key instanceof \fpoirotte\Pssht\Key\KeyInterface) {
            $key = $this->getIdentifier($key);
        }
        if (!is_string($key)) {
            throw new \InvalidArgumentException();
        }

        return isset($this->keys[$user][$key]);
    }

    /**
     * Return the number of keys currently registered
     * for the given user.
     *
     *  \param string $user
     *      User whose keys must be counted.
     *
     *  \retval int
     *      Number of available keys for the given user.
     */
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
