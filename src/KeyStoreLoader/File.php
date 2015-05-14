<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\KeyStoreLoader;

use fpoirotte\Pssht\KeyLoader\Openssh;
use fpoirotte\Pssht\KeyLoader\Putty;

/**
 * Public keys loader from a file.
 */
class File
{
    /// Storage object for the keys.
    protected $store;

    /**
     * Construct a new loader.
     *
     *  \param fpoirotte::Pssht::KeyStore $store
     *      (optional) Object where the keys will be stored.
     *      If omitted, a new (empty) store is automatically
     *      created.
     */
    public function __construct(\fpoirotte\Pssht\KeyStore $store = null)
    {
        if ($store === null) {
            $store = new \fpoirotte\Pssht\KeyStore();
        }

        $this->store    = $store;
    }

    /**
     * Load the keys in the given file as if they belonged
     * to the specified user.
     *
     *  \param string $user
     *      User the keys belong to.
     *
     *  \param string $file
     *      File containing the keys to load.
     *      It should follow the format of OpenSSH's
     *      authorized_keys file.
     *
     *  \retval File
     *      Returns this loader.
     */
    public function load($user, $file)
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($file)) {
            throw new \InvalidArgumentException();
        }

        $logging    = \Plop\Plop::getInstance();

        $lines      = file($file);
        if ($lines === false) {
            $logging->debug(
                'Ignoring unreadable file "%(file)s"',
                array(
                    'user' => $user,
                    'file' => $file,
                )
            );
            return $this;
        }

        $count = 0;
        try {
            $this->store->add($user, Putty::loadPublic(implode('', $lines)));
            $count++;
        } catch (\InvalidArgumentException $e) {
            foreach ($lines as $line) {
                // Ignore empty lines and lines
                // starting with '#' (comments).
                if (trim($line) === '' || $line[0] === '#') {
                    continue;
                }
                $this->store->add($user, Openssh::loadPublic(rtrim($line)));
                $count++;
            }
        }
        $logging->debug(
            'Imported %(count)d identities for "%(user)s" from "%(file)s"',
            array(
                'count' => $count,
                'user' => $user,
                'file' => $file,
            )
        );
        return $this;
    }

    /**
     * Bulk-load keys.
     *
     *  \param array $bulk
     *      An array with information on the keys to load.
     *      The keys in the array indicate users while
     *      the values contain (an array of) files containing
     *      the keys to load.
     *
     *  \retval File
     *      Returns this loader.
     */
    public function loadBulk(array $bulk)
    {
        foreach ($bulk as $user => $files) {
            if (!is_string($user)) {
                throw new \InvalidArgumentException();
            }

            if (!is_array($files) && !is_string($files)) {
                throw new \InvalidArgumentException();
            }

            $files = (array) $files;
            foreach ($files as $file) {
                $this->load($user, $file);
            }
        }
        return $this;
    }

    /**
     * Return the key store associated with this loader.
     *
     *  \retval fpoirotte::Pssht::KeyStore
     *      The store for this loader.
     */
    public function getStore()
    {
        return $this->store;
    }
}
