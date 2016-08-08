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
 * A singleton that gives access to supported algorithms.
 */
class Algorithms
{
    /// Array with currently available algorithms.
    protected $algos;

    /// A backup of $algos when it was first populated.
    protected $savedAlgos;

    /// Mapping between algorithm types and their corresponding interface.
    protected $interfaces;

    /**
     * Construct the only instance of this singleton.
     */
    private function __construct()
    {
        $this->interfaces = array(
            'MAC'           => '\\fpoirotte\\Pssht\\MAC\\MACInterface',
            'Compression'   => '\\fpoirotte\\Pssht\\Compression\\CompressionInterface',
            'Key'           => '\\fpoirotte\\Pssht\\Key\\KeyInterface',
            'KEX'           => '\\fpoirotte\\Pssht\\KEX\\KEXInterface',
            'Encryption'    => '\\fpoirotte\\Pssht\\Encryption\\EncryptionInterface',
        );

        $this->algos = array(
            'MAC'           => array(),
            'Compression'   => array(),
            'Key'           => array(),
            'KEX'           => array(),
            'Encryption'    => array(),
        );

        $logging = \Plop\Plop::getInstance();
        foreach (array_keys($this->algos) as $type) {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    __DIR__ . DIRECTORY_SEPARATOR . $type,
                    \FilesystemIterator::UNIX_PATHS             |
                    \FilesystemIterator::KEY_AS_PATHNAME        |
                    \FilesystemIterator::CURRENT_AS_FILEINFO    |
                    \FilesystemIterator::SKIP_DOTS
                )
            );
            $dirLen = strlen(__DIR__ . DIRECTORY_SEPARATOR . $type);
            foreach ($it as $entry) {
                if (!$entry->isFile()) {
                    continue;
                }

                if (substr($entry->getBasename(), -4) !== '.php') {
                    continue;
                }

                $name   = (string) substr($entry->getPathname(), $dirLen, -4);
                $name   = str_replace('/', '\\', $name);
                $class  = $this->getValidClass($type, $name);
                if ($class === null) {
                    continue;
                }

                $algo = $this->getValidAlgorithm($class);
                if ($algo === null) {
                    continue;
                }

                $logging->debug(
                    'Adding "%(algo)s" (from %(class)s) to supported %(type)s algorithms',
                    array('type' => $type, 'algo' => $algo, 'class' => $class)
                );
                $this->algos[$type][$algo] = $class;
            }
            uksort($this->algos[$type], array('self', 'sortAlgorithms'));
        }
        $this->savedAlgos = $this->algos;
    }

    /// Prevent cloning of the singleton.
    public function __clone()
    {
        throw new \RuntimeException();
    }

    /**
     * Retrieve the singleton.
     *
     *  \retval Algorithms
     *      The singleton.
     */
    public static function factory()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * Check for valid classnames.
     *
     *  \param string $type
     *      Expected type of class (algorithm name).
     *
     *  \param string $name
     *      Name of the class.
     *
     *  \retval string
     *      Full classname (with namespace).
     *
     *  \retval null
     *      No valid class found with this type and name.
     */
    protected function getValidClass($type, $name)
    {
        $logging = \Plop\Plop::getInstance();
        $w = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_1234567890\\';
        if (!is_string($name) || strspn($name, $w) !== strlen($name)) {
            $logging->debug(
                'Skipping %(type)s algorithm "%(name)s" (invalid class name)',
                array('type' => $type, 'name' => $name)
            );
            return null;
        }

        // Non-existing classes.
        $class = "\\fpoirotte\\Pssht\\$type$name";
        if (!class_exists($class)) {
            if (!interface_exists($class)) {
                $logging->debug(
                    'Skipping %(type)s algorithm "%(name)s" (class does not exist)',
                    array('type' => $type, 'name' => $name)
                );
            }
            return null;
        }

        // Abstract classes.
        $reflector = new \ReflectionClass($class);
        if ($reflector->isAbstract()) {
            return null;
        }

        // Classes that implement AvailabilityInterface
        // where the algorithm is not currently available.
        $iface = '\\fpoirotte\\Pssht\\Algorithms\\AvailabilityInterface';
        if ($reflector->implementsInterface($iface) && !$class::isAvailable()) {
            $logging->debug(
                'Skipping %(type)s algorithm "%(name)s" (not available)',
                array('type' => $type, 'name' => $name)
            );
            return null;
        }

        // Classes that do not implement the proper interface.
        $iface = $this->interfaces[$type];
        if ($iface !== null && !$reflector->implementsInterface($iface)) {
            $logging->debug(
                'Skipping %(type)s algorithm "%(name)s" (invalid interface)',
                array('type' => $type, 'name' => $name)
            );
            return null;
        }

        return $class;
    }

    /**
     * Check for valid algorithm names.
     *
     *  \param string $class
     *      Name of the class whose algorithm name must be checked.
     *
     *  \retval string
     *      The class' algorithm name.
     *
     *  \retval null
     *      No valid algorithm name for the given class.
     */
    protected function getValidAlgorithm($class)
    {
        $logging = \Plop\Plop::getInstance();
        $w =    'abcdefghijklmnopqrstuvwxyz' .
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
                '1234567890-@.';
        $name = $class::getName();
        if (!is_string($name) || strspn($name, $w) !== strlen($name)) {
            $logging->debug(
                'Skipping algorithm "%(name)s" (invalid algorithm name)',
                array('name' => $name)
            );
            return null;
        }
        return $name;
    }

    /**
     * Register a new algorithm.
     *
     *  \param string $type
     *      Type of algorithm provided by the class.
     *
     *  \param string|object $class
     *      Class or object that provides the algorithm.
     *
     *  \retval Algorithms
     *      Returns the singleton.
     *
     *  \note
     *      A class registered with this method will overwrite
     *      any previously registered class with the same
     *      algorithm name.
     */
    public function register($type, $class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        if (!is_string($class) || !class_exists($class)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($type) || !isset($this->algos[$type])) {
            throw new \InvalidArgumentException();
        }
        $name = $this->getValidAlgorithm($class);
        if ($name === null) {
            throw new \InvalidArgumentException();
        }

        $this->algos[$type][$name] = $class;
        uksort($this->algos[$type], array('self', 'sortAlgorithms'));
        return $this;
    }

    /**
     * Unregister an algorithm.
     *
     *  \param string $type
     *      Algorithm type.
     *
     *  \param string $name
     *      Name of the algorithm to unregister.
     *
     *  \retval Algorithms
     *      Returns the singleton.
     */
    public function unregister($type, $name)
    {
        if (!is_string($type) || !isset($this->algos[$type])) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException();
        }
        unset($this->algos[$type][$name]);
        return $this;
    }

    /**
     * Restore an algorithm.
     *
     * Reset the class in charge of providing
     * a given algorithm to its initial value.
     *
     *  \param string $type
     *      Algorithm type.
     *
     *  \param string $name
     *      Name of the algorithm to restore.
     *
     *  \retval Algorithms
     *      Returns the singleton.
     */
    public function restore($type, $name)
    {
        if (!is_string($type) || !isset($this->algos[$type])) {
            throw new \InvalidArgumentException();
        }
        if (isset($this->savedAlgos[$type][$name])) {
            $this->algos[$type][$name] = $this->savedAlgos[$type][$name];
        }
        return $this;
    }

    /**
     * Get a list of all registered algorithms
     * with the given type.
     *
     *  \param string $type
     *      Type of algorithms to retrieve.
     *
     *  \retval array
     *      A list with the names of all the algorithms
     *      currently registered with the given type.
     */
    public function getAlgorithms($type)
    {
        if (!is_string($type) || !isset($this->algos[$type])) {
            throw new \InvalidArgumentException();
        }
        return array_keys($this->algos[$type]);
    }

    /**
     * Get a list of all registered classes
     * with the given type.
     *
     *  \param string $type
     *      Type of algorithms to retrieve.
     *
     *  \retval array
     *      A list with the names of the classes currently
     *      registered providing algorithms of the given type.
     */
    public function getClasses($type)
    {
        if (!is_string($type) || !isset($this->algos[$type])) {
            throw new \InvalidArgumentException();
        }
        return $this->algos[$type];
    }

    /**
     * Get the class responsible for providing
     * the algorithm with the given type and name.
     *
     *  \param string $type
     *      Type of algorithm to retrieve.
     *
     *  \param string $name
     *      Name of the algorithm.
     *
     *  \retval string
     *      Full name (with namespace) of the class
     *      providing the given algorithm.
     *
     *  \retval null
     *      No class provides an algorithm with the given
     *      type and name.
     */
    public function getClass($type, $name)
    {
        if (!is_string($type) || !isset($this->algos[$type])) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException();
        }
        if (!isset($this->algos[$type][$name])) {
            return null;
        }
        return $this->algos[$type][$name];
    }

    /**
     * Sort algorithms based on preferences.
     *
     *  \param string $a
     *      Name of the first algorithm.
     *
     *  \param string $b
     *      Name of the second algorithm.
     *
     *  \retval int
     *      An integer that is less than zero if the first algorithm
     *      should be preferred, equal to zero if both algorithms have
     *      the same preference and greater than zero when the second
     *      algorithm should be preferred.
     */
    public static function sortAlgorithms($a, $b)
    {
        static $preferences = array(
            // DH (KEX)
            'curve25519-sha256@libssh.org',
            'ecdh-sha2-nistp256',
            'ecdh-sha2-nistp384',
            'ecdh-sha2-nistp521',
            'diffie-hellman-group-exchange-sha256',
            'diffie-hellman-group-exchange-sha1',
            'diffie-hellman-group14-sha1',
            'diffie-hellman-group1-sha1',

            // Public Key
            'ecdsa-sha2-nistp256-cert-v01@openssh.com',
            'ecdsa-sha2-nistp384-cert-v01@openssh.com',
            'ecdsa-sha2-nistp521-cert-v01@openssh.com',
            'ecdsa-sha2-nistp256',
            'ecdsa-sha2-nistp384',
            'ecdsa-sha2-nistp521',
            'ssh-ed25519-cert-v01@openssh.com',
            'ssh-rsa-cert-v01@openssh.com',
            'ssh-dss-cert-v01@openssh.com',
            'ssh-rsa-cert-v00@openssh.com',
            'ssh-dss-cert-v00@openssh.com',
            'ssh-ed25519',
            'ssh-rsa',
            'ssh-dss',

            // Encryption
            'aes128-ctr',
            'aes192-ctr',
            'aes256-ctr',
            'aes128-gcm@openssh.com',
            'aes256-gcm@openssh.com',
            'chacha20-poly1305@openssh.com',
            'arcfour256',
            'arcfour128',
            'aes128-cbc',
            '3des-cbc',
            'blowfish-cbc',
            'cast128-cbc',
            'aes192-cbc',
            'aes256-cbc',
            'arcfour',
            'rijndael-cbc@lysator.liu.se',

            // MAC
            'umac-64-etm@openssh.com',
            'umac-128-etm@openssh.com',
            'hmac-sha2-256-etm@openssh.com',
            'hmac-sha2-512-etm@openssh.com',
            'hmac-sha1-etm@openssh.com',
            'umac-64@openssh.com',
            'umac-128@openssh.com',
            'hmac-sha2-256',
            'hmac-sha2-512',
            'hmac-sha1',
            'hmac-md5-etm@openssh.com',
            'hmac-ripemd160-etm@openssh.com',
            'hmac-sha1-96-etm@openssh.com',
            'hmac-md5-96-etm@openssh.com',
            'hmac-md5',
            'hmac-ripemd160',
            'hmac-ripemd160@openssh.com',
            'hmac-sha1-96',
            'hmac-md5-96',

            // Compression
            'none',
            'zlib@openssh.com',
            'zlib',
        );

        $iA = array_search($a, $preferences, true);
        $iB = array_search($b, $preferences, true);
        if ($iA === false) {
            return ($iB === false ? 0 : 1);
        }
        if ($iB === false) {
            return -1;
        }
        return ($iA - $iB);
    }
}
