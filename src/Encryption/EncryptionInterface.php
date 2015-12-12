<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption;

/**
 * Interface for an encryption/decryption algorithm.
 */
interface EncryptionInterface extends \fpoirotte\Pssht\Algorithms\AlgorithmInterface
{
    /**
     * Construct an encryption/decryption algorithm.
     *
     *  \param string $iv
     *      Initialization vector for the algorithm.
     *
     *  \param string $key
     *      Encryption/decrytion key.
     */
    public function __construct($iv, $key);

    /**
     * Get the algorithm's key size.
     *
     *  \retval int
     *      Key size (in bytes).
     */
    public static function getKeySize();

    /**
     * Get the algorithm's IV size.
     *
     *  \retval int
     *      Initialization vector size (in bytes).
     */
    public static function getIVSize();

    /**
     * Get the algorithm's block size.
     *
     *  \retval int
     *      Block size (in bytes).
     */
    public static function getBlockSize();

    /**
     * Encrypt data using the algorithm.
     *
     *  \param int $seqno
     *      Sequence number.
     *
     *  \param string $data
     *      Data to encrypt.
     *
     *  \retval string
     *      Encrypted data.
     */
    public function encrypt($seqno, $data);

    /**
     * Decrypt data using the algorithm.
     *
     *  \param int $seqno
     *      Sequence number.
     *
     *  \param string $data
     *      Data to decrypt.
     *
     *  \retval string
     *      Decrypted data.
     */
    public function decrypt($seqno, $data);
}
