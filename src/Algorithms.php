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
    const CLASS_WHITELIST = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_1234567890';
    const ALGO_WHITELIST  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-1234567890@.';

    protected $_algos;

    private function __construct()
    {
        $this->_algos = array(
            'MAC'           => array(),
            'Compression'   => array(),
            'PublicKey'     => array(),
            'KEX'           => array(),
            'Encryption'    => array(),
            'Services'      => array(),
        );

        foreach (array_keys($this->_algos) as $type) {
            $it = new \DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . $type);
            foreach ($it as $entry) {
                $name = $entry->getBasename('.php');
                if (!is_string($name) || strspn($name, self::CLASS_WHITELIST) !== strlen($name))
                    continue;
                if (!$entry->isFile())
                    continue;
                $class = "\\Clicky\\Pssht\\$type\\$name";
                if (!class_exists($class))
                    continue;

                $algo = $class::getName();
                if (!is_string($algo) || strspn($algo, self::ALGO_WHITELIST) !== strlen($algo))
                    continue;

                $this->_algos[$type][$algo] = "\\Clicky\\Pssht\\$type\\$name";
            }
        }
    }

    public function __clone()
    {
        throw new \RuntimeException();
    }

    static public function factory()
    {
        static $instance = NULL;
        if ($instance === NULL) {
            $instance = new self();
        }
        return $instance;
    }

    public function register($type, $class)
    {
        if (!is_string($class) || !class_exists($class))
            throw new \InvalidArgumentException();
        if (!is_string($type) || !isset($this->_algos[$type]))
            throw new \InvalidArgumentException();
        $name = $class::getName();
        if (!is_string($name) || strspn($name, self::ALGO_WHITELIST) !== strlen($name))
            throw new \InvalidArgumentException();

        $this->_algos[$type][$name] = $class;
        return $this;
    }

    public function unregister($type, $name)
    {
        if (!is_string($type) || !isset($this->_algos[$type]))
            throw new \InvalidArgumentException();
        if (!is_string($name))
            throw new \InvalidArgumentException();
        unset($this->_algos[$type][$name]);
        return $this;
    }

    public function restore($type, $name)
    {
        if (!is_string($type) || !isset($this->_algos[$type]))
            throw new \InvalidArgumentException();
        if (!is_string($name) || strspn($name, self::CLASS_WHITELIST) !== strlen($name))
            throw new \InvalidArgumentException();

        if (class_exists("\\Clicky\\Pssht\\$type\\$name")) {
            $this->_algos[$type][$name] = "\\Clicky\\Pssht\\$type\\$name";
        }
        return $this;
    }

    public function getAlgorithms($type)
    {
        if (!is_string($type) || !isset($this->_algos[$type]))
            throw new \InvalidArgumentException();
        return array_keys($this->_algos[$type]);
    }

    public function getClasses($type)
    {
        if (!is_string($type) || !isset($this->_algos[$type]))
            throw new \InvalidArgumentException();
        return $this->_algos[$type];
    }

    public function getClass($type, $name)
    {
        if (!is_string($type) || !isset($this->_algos[$type]))
            throw new \InvalidArgumentException();
        if (!isset($this->_algos[$type][$name]))
            return NULL;
        return $this->_algos[$type][$name];
    }
}

