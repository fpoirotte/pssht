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

class Algorithms
{
    protected $algos;
    protected $savedAlgos;
    protected $interfaces;

    private function __construct()
    {
        $this->interfaces = array(
            'MAC'           => '\\Clicky\\Pssht\\MACInterface',
            'Compression'   => '\\Clicky\\Pssht\\CompressionInterface',
            'PublicKey'     => '\\Clicky\\Pssht\\PublicKeyInterface',
            'KEX'           => '\\Clicky\\Pssht\\KEXInterface',
            'Encryption'    => '\\Clicky\\Pssht\\EncryptionInterface',
        );

        $this->algos = array(
            'MAC'           => array(),
            'Compression'   => array(),
            'PublicKey'     => array(),
            'KEX'           => array(),
            'Encryption'    => array(),
        );

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

                $this->algos[$type][$algo] = $class;
            }
            uksort($this->algos[$type], array('self', 'sortAlgorithms'));
        }
        $this->savedAlgos = $this->algos;
    }

    public function __clone()
    {
        throw new \RuntimeException();
    }

    public static function factory()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    protected function getValidClass($type, $name)
    {
        $w = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_1234567890\\';
        if (!is_string($name) || strspn($name, $w) !== strlen($name)) {
            return null;
        }

        // Non-existing classes.
        $class = "\\Clicky\\Pssht\\$type$name";
        if (!class_exists($class)) {
            return null;
        }

        // Abstract classes.
        $reflector = new \ReflectionClass($class);
        if ($reflector->isAbstract()) {
            return null;
        }

        // Classes that implement AvailabilityInterface
        // where the algorithm is not currently available.
        $iface = '\\Clicky\\Pssht\\AvailabilityInterface';
        if ($reflector->implementsInterface($iface) && !$class::isAvailable()) {
            return null;
        }

        // Classes that do not implement the proper interface.
        $iface = $this->interfaces[$type];
        if ($iface !== null && !$reflector->implementsInterface($iface)) {
            return null;
        }

        return $class;
    }

    protected function getValidAlgorithm($class)
    {
        $w =    'abcdefghijklmnopqrstuvwxyz' .
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
                '1234567890-@.';
        $name = $class::getName();
        if (!is_string($name) || strspn($name, $w) !== strlen($name)) {
            return null;
        }
        return $name;
    }

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

    public function getAlgorithms($type)
    {
        if (!is_string($type) || !isset($this->algos[$type])) {
            throw new \InvalidArgumentException();
        }
        return array_keys($this->algos[$type]);
    }

    public function getClasses($type)
    {
        if (!is_string($type) || !isset($this->algos[$type])) {
            throw new \InvalidArgumentException();
        }
        return $this->algos[$type];
    }

    public function getClass($type, $name)
    {
        if (!is_string($type) || !isset($this->algos[$type])) {
            throw new \InvalidArgumentException();
        }
        if (!isset($this->algos[$type][$name])) {
            return null;
        }
        return $this->algos[$type][$name];
    }

    public static function sortAlgorithms($a, $b)
    {
        static $preferences = array(
            // KEX
            'ecdh-sha2-nistp256',
            'ecdh-sha2-nistp384',
            'ecdh-sha2-nistp521',
            'diffie-hellman-group-exchange-sha256',
            'diffie-hellman-group-exchange-sha1',
            'diffie-hellman-group14-sha1',
            'diffie-hellman-group1-sha1',

            // PublicKey
            'ssh-rsa-cert-v01@openssh.com',
            'ssh-rsa-cert-v00@openssh.com',
            'ssh-rsa',
            'ecdsa-sha2-nistp256-cert-v01@openssh.com',
            'ecdsa-sha2-nistp384-cert-v01@openssh.com',
            'ecdsa-sha2-nistp521-cert-v01@openssh.com',
            'ssh-dss-cert-v01@openssh.com',
            'ssh-dss-cert-v00@openssh.com',
            'ecdsa-sha2-nistp256',
            'ecdsa-sha2-nistp384',
            'ecdsa-sha2-nistp521',
            'ssh-dss',

            // Encryption
            'aes128-ctr',
            'aes192-ctr',
            'aes256-ctr',
            'arcfour256',
            'arcfour128',
            'aes128-gcm@openssh.com',
            'aes256-gcm@openssh.com',
            'aes128-cbc',
            '3des-cbc',
            'blowfish-cbc',
            'cast128-cbc',
            'aes192-cbc',
            'aes256-cbc',
            'arcfour',
            'rijndael-cbc@lysator.liu.se',

            // MAC
            'hmac-sha1-etm@openssh.com',
            'hmac-md5-etm@openssh.com',
            'umac-64-etm@openssh.com',
            'umac-128-etm@openssh.com',
            'hmac-sha2-256-etm@openssh.com',
            'hmac-sha2-512-etm@openssh.com',
            'hmac-ripemd160-etm@openssh.com',
            'hmac-sha1-96-etm@openssh.com',
            'hmac-md5-96-etm@openssh.com',
            'hmac-sha1',
            'hmac-md5',
            'umac-64@openssh.com',
            'umac-128@openssh.com',
            'hmac-sha2-256',
            'hmac-sha2-512',
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
