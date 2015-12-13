<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\KeyLoader;

/**
 * Interface for a key loader.
 *
 * This interface can be used to load both public
 * and private keys.
 */
interface KeyLoaderInterface
{
    /**
     * Load a private key.
     *
     *  \param string $data
     *      Data representing the private key to load.
     *
     *  \param string $passphrase
     *      (optional) Passphrase for the private key.
     *
     *  \retval KeyInterface
     *      Private key as loaded from the data.
     */
    public static function loadPrivate($data, $passphrase = '');

    /**
     * Load a public key.
     *
     *  \param string $data
     *      Data representing the public key to load.
     *
     *  \retval KeyInterface
     *      Public key as loaded from the data.
     */
    public static function loadPublic($data);
}
