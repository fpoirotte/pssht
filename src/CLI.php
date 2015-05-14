<?php
/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
declare(ticks=1);

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

function escape($data)
{
    return addcslashes($data, "\x00..\x1F\x7F..\xFF");
}

function signal_handler($signo)
{
    $logging = \Plop\Plop::getInstance();
    $logging->info(
        "Received signal #%d. Shutting down...",
        array($signo)
    );
    exit(0);
}

function main($confFile = 'pssht.xml')
{
    $hasSignalDispatch = function_exists('pcntl_signal_dispatch');
    if (extension_loaded('pcntl')) {
        pcntl_signal(SIGTERM, 'signal_handler');
        pcntl_signal(SIGINT, 'signal_handler');
    }

    $home = getenv('HOME');
    $user = getenv('USER');
    if (extension_loaded('posix')) {
        $entry = posix_getpwuid(posix_geteuid());
        $home = $entry['dir'];
        $user = $entry['name'];
    }

    // DIC
    $container  = new ContainerBuilder();
    $container->setParameter('CWD', getcwd());
    $container->setParameter('HOME', $home);
    $container->setParameter('USER', $user);
    $container->setParameter('pssht.base_dir', dirname(__DIR__));

    $loader     = new XmlFileLoader($container, new FileLocator(getcwd()));
    try {
        $loader->load($confFile);
    } catch (\InvalidArgumentException $e) {
        $logging = \Plop\Plop::getInstance();
        $logging->error($e->getMessage());
        exit(1);
    }
    $container->get('logging', ContainerInterface::NULL_ON_INVALID_REFERENCE);
    $logging    = \Plop\Plop::getInstance();
    $logging->info(
        "pssht %s is starting (PID %d)",
        array(PSSHT_VERSION, getmypid())
    );

    // Elliptic curves
    \fpoirotte\Pssht\ECC\Curve::initialize();

    // Pre-load algorithms
    \fpoirotte\Pssht\Algorithms::factory();

    // Sockets
    $sockets    = array('servers' => array(), 'clients' => array());
    $clients    = array();

    $listen     = (array) $container->getParameter('listen');
    foreach ($listen as $spec) {
        $socket                 = stream_socket_server("tcp://$spec");
        $sockets['servers'][]   = $socket;
        $address                = stream_socket_get_name($socket, false);
        $logging->info("Listening for new connections on %s", array($address));
    }

    while (true) {
        $read   = array_merge($sockets['servers'], $sockets['clients']);
        $except = $read;
        $write  = array();

        foreach ($clients as $id => $client) {
            if (count($client->getEncoder()->getBuffer())) {
                $write[] = $sockets['clients'][$id];
            }
        }

        if (@stream_select($read, $write, $except, 2) === false) {
            $logging->error(
                'Error while waiting for activity on sockets: %s',
                array(socket_strerror(socket_last_error()))
            );
            continue;
        }

        if ($hasSignalDispatch) {
            function_exists('pcntl_signal_dispatch');
        }

        foreach ($read as $socket) {
            if (in_array($socket, $sockets['servers'], true)) {
                $new = stream_socket_accept($socket);
                if ($new === false) {
                    $logging->error(
                        'Could not accept new client: %s',
                        array(socket_strerror(socket_last_error()))
                    );
                    continue;
                }

                for ($id = 0; isset($sockets['clients'][$id]); $id++) {
                    // Nothing to do.
                }
                $sockets['clients'][$id] = $new;
                $peer   = stream_socket_get_name($new, true);
                $logging->info(
                    '#%(id)d New client connected from %(peer)s',
                    array('id' => $id, 'peer' => $peer)
                );
                $client         = $container->get('client');
                $clients[$id]   = $client;
                $client->setAddress(substr($peer, 0, strrpos($peer, ':')));
                continue;
            }

            $data   = fread($socket, 8192);
            $peer   = stream_socket_get_name($socket, true);
            $close  = false;
            if ($data === '') {
                $id     = array_search($socket, $sockets['clients'], true);
                $logging->info(
                    '#%(id)d Client disconnected from %(peer)s (socket closed)',
                    array('id' => $id, 'peer' => $peer)
                );
                $close = true;
            } elseif ($data !== false) {
                $length = strlen($data);
                $id     = array_search($socket, $sockets['clients'], true);
                $clients[$id]->getDecoder()->getBuffer()->push($data);

                $logging->log(
                    5,
                    '#%(id)d Received %(length)d bytes from %(peer)s',
                    array('id' => $id, 'peer' => $peer, 'length' => $length)
                );
                $logging->log(5, '%s', array(escape($data)));

                // Process messages in the buffer.
                try {
                    $close = true;
                    while ($clients[$id]->readMessage()) {
                        // Each message gets processed by readMessage().
                    }
                    // Never reached when an exception
                    // is raised by readMessage().
                    $close = false;
                } catch (\fpoirotte\Pssht\Messages\DISCONNECT $e) {
                    $logging->info(
                        '#%(id)d Client disconnected from %(peer)s ' .
                        '(DISCONNECT message received)',
                        array('id' => $id, 'peer' => $peer)
                    );
                } catch (\Exception $e) {
                    $logging->exception(
                        '#%(id)d Client disconnected from %(peer)s ' .
                        'due to exception',
                        $e,
                        array('id' => $id, 'peer' => $peer)
                    );
                }
            }

            if ($close) {
                fclose($socket);
                unset($sockets['clients'][$id]);
                unset($clients[$id]);
            }
        }

        foreach ($write as $socket) {
            $id = array_search($socket, $sockets['clients'], true);
            if ($id === false) {
                continue;
            }

            $peer   = stream_socket_get_name($socket, true);
            $buffer = $clients[$id]->getEncoder()->getBuffer();
            $size   = count($buffer);
            $data   = $buffer->get($size);
            while ($size > 0) {
                $written = fwrite($socket, $data);
                if ($written === false) {
                    break;
                }

                $logging->log(
                    5,
                    "#%(id)d Sent %(written)d bytes to %(peer)s",
                    array('id' => $id, 'peer' => $peer, 'written' => $written)
                );
                $logging->log(5, '%s', array(escape(substr($data, 0, $written))));
                $data   = substr($data, $written);
                $clients[$id]->updateWriteStats($written);
                $size  -= $written;
            }

            if (!$clients[$id]->isConnected()) {
                fclose($socket);
                unset($sockets['clients'][$id]);
                unset($clients[$id]);
            }
        }
    }
}
