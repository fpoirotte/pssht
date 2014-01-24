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
                if (!$entry->isFile())
                    continue;
                $name   = $entry->getBasename('.php');
                $class  = $this->_getClass($type, $name);
                if ($class === NULL)
                    continue;
                $algo = $this->_getAlgo($class);
                if ($algo === NULL)
                    continue;
                $this->_algos[$type][$algo] = $class;
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

    protected function _getClass($type, $name)
    {
        $w = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_1234567890';
        if (!is_string($name) || strspn($name, $w) !== strlen($name))
            return NULL;
        $class = "\\Clicky\\Pssht\\$type\\$name";
        if (!class_exists($class))
            return NULL;
        $reflector = new ReflectionClass($class);
        return ($reflector->isAbstract() ? NULL : $class);
    }

    protected function _getAlgo($class)
    {
        $w =    'abcdefghijklmnopqrstuvwxyz' .
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
                '1234567890-@.';
        $name = $class::getName();
        if (!is_string($name) || strspn($name, $w) !== strlen($name))
            return NULL;
        return $name;
    }

    public function register($type, $class)
    {
        if (is_object($class))
            $class = get_class($class);
        if (!is_string($class) || !class_exists($class))
            throw new \InvalidArgumentException();
        if (!is_string($type) || !isset($this->_algos[$type]))
            throw new \InvalidArgumentException();
        $name = $this->_getAlgo($class);
        if ($name === NULL)
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
        $class = $this->_getClass($type, $name);
        if ($class !== NULL)
            $this->_algos[$type][$name] = $class;
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

