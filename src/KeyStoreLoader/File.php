<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\KeyStoreLoader;

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
     *  \param KeyStore $store
     *      (optional) Object where the keys will be stored.
     *      If omitted, a new (empty) store is automatically
     *      created.
     */
    public function __construct(\Clicky\Pssht\KeyStore $store = null)
    {
        if ($store === null) {
            $store = new \Clicky\Pssht\KeyStore();
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

        $algos = \Clicky\Pssht\Algorithms::factory();
        $types = array(
            'ssh-dss',
            'ssh-rsa',
#            'ecdsa-sha2-nistp256',
#            'ecdsa-sha2-nistp384',
#            'ecdsa-sha2-nistp521',
        );

        foreach (file($file) as $line) {
            $fields = explode(' ', preg_replace('/\\s+/', ' ', trim($line)));
            $max    = count($fields);
            for ($i = 0; $i < $max; $i++) {
                if (in_array($fields[$i], $types, true)) {
                    $cls = $algos->getClass('PublicKey', $fields[$i]);
                    $this->store->add($user, $cls::loadPublic($fields[$i+1]));
                    break;
                }
            }
        }

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
     *  \retval KeyStore
     *      The store for this loader.
     */
    public function getStore()
    {
        return $this->store;
    }
}
