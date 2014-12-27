pssht
=====

pssht is a PHP library that provides an SSH server that can be embedded
in other applications.

What we're aiming for:

*   Clean code (eg. PSR-2 & PSR-4 compliance, tests, ...)
*   Extensibility
*   Interoperability with as much SSH clients as possible, but mainly
    with the OpenSSH client

What we're not specifically aiming for, but still interested in:

*   Completeness (support for TCP port forwarding, TUN/TAP tunneling,
    the scp/sftp subsystems, ...)
*   Strong security (peer reviews, security audits, ...)


Disclaimer
----------

This should be obvious from the get-go, but **DO NOT USE** pssht in production.
This project merely exists for two reasons:

-   First, I wanted to provide a """somewhat secure""" cross-platform way
    to expose `Erebot internals <https://github.com/Erebot/Erebot>`_
    for introspection purposes and I did not want to install an external
    SSH daemon.
-   Secondly, I wanted to learn more about the SSH protocol itself.

The implementation did not pass any specific security audit. In addition,
no attempt has been made to avoid some common classes of vulnerabilities, eg.
`timing attacks <http://blog.ircmaxell.com/2014/11/its-all-about-time.html>`_.
Not to mention that the PHP interpreter itself is known to be frequently
subject to `vulnerabilities of its own
<http://www.cvedetails.com/product/128/PHP-PHP.html?vendor_id=74>`_.

If you are looking for an SSH daemon with thorough testing and code
audits to integrate with your PHP code, we recommend that you look into
the `OpenSSH project <http://www.openssh.com/>`_.

If you still aren't convinced that you shouldn't use this code in production,
see `this reddit page
<http://www.reddit.com/r/lolphp/comments/1yvm6v/php_can_do_anything_what_about_some_ssh_mtgox>`_
which relates part of the story of a project similar to pssht by MtGox's CEO.

In no event shall the authors of pssht be liable for anything that happens
while using this library. Please read the `license`_ for the full disclaimer.


Installation & Usage
--------------------

Download the `composer.phar <https://getcomposer.org/composer.phar>`_
executable or use the installer.

..  sourcecode:: console

    $ curl -sS https://getcomposer.org/installer | php

Create a ``composer.json`` that requires pssht.

..  sourcecode:: json

    {
        "require": {
            "fpoirotte/pssht": "dev-master"
        }
    }

Run Composer.

..  sourcecode:: console

    $ php composer.phar install

Run the server.

..  sourcecode:: console

    $ php bin/pssht


Configuration
-------------

pssht uses the Dependency Injection component from the Symfony2 framework
for its configuration. Have a look at the default `pssht.xml
<https://github.com/fpoirotte/pssht/blob/master/pssht.xml>`_
configuration file for ways to customize pssht.


Compatibility
-------------

pssht supports the mechanisms and algorithms defined in the following
documents for compatibility with other Secure Shell implementations:

-   :rfc:`4250` |---| SSH Protocol Assigned Numbers
-   :rfc:`4251` |---| SSH Protocol Architecture
-   :rfc:`4252` |---| SSH Authentication Protocol
-   :rfc:`4253` |---| SSH Transport Layer Protocol
-   :rfc:`4254` |---| SSH Connection Protocol
-   :rfc:`4344` |---| SSH Transport Layer Encryption Modes
-   :rfc:`4345` |---| Improved Arcfour Modes for the SSH Transport Layer Protocol
-   :rfc:`4462` |---| SSH Public Key File Format
-   :rfc:`5647` |---| AES Galois Counter Mode for the SSH Transport Layer Protocol
-   :rfc:`5656` |---| Elliptic Curve Algorithm Integration in SSH
-   :rfc:`6668` |---| SHA-2 Data Integrity Algorithms
-   `draft-miller-secsh-umac-01`_
    |---| UMAC in the SSH Transport Layer Protocol
-   `draft-miller-secsh-compression-delayed-00`_
    |---| Delayed compression until after authentication
-   `OpenSSH PROTOCOL`_
    |---| Various OpenSSH extensions to the SSH protocol
-   `OpenSSH private key format`_
    |---| Specification for OpenSSH's private key format
-   `ChaCha20-Poly1305`_
    |---| The ``chacha20-poly1305@openssh.com`` authenticated encryption cipher
-   `Ed25519 curve`_
    |---| Twisted Edwards Curve 2\*\*255-19
-   `Curve25519 curve`_
    |---| Montgomery Curve 2\*\*255-19

The rest of this section describes precisely which algorithms and features
are supported.

**TL;DR** here's a feature chart for comparison with OpenSSH:

-   |[ ]| Services (2 in pssht; ? in OpenSSH)
-   |[ ]| Authentication methods (4 in pssht; ? in OpenSSH)
-   |[ ]| Key exchange methods (6 in pssht; 8 in OpenSSH)
-   |[x]| Encryption algorithms (34 in pssht; 16 in OpenSSH) [#null]_
-   |[x]| MAC algorithms (20 in pssht; 19 in OpenSSH) [#null]_
-   |[ ]| Public key algorithms (6 in pssht; 14 in OpenSSH)
-   |[x]| Compression algorithms (2 in pssht; 2 in OpenSSH) [#null]_

..  [#null] The "none" algorithm has been excluded from those counts.

Services
~~~~~~~~

The following services are supported:

-   ``ssh-userauth``
-   ``ssh-connection``

Authentication methods
~~~~~~~~~~~~~~~~~~~~~~

The following authentication methods are supported:

-   ``publickey``
-   ``password``
-   ``hostbased``
-   ``none``

Key exchange methods
~~~~~~~~~~~~~~~~~~~~

The following key exchange methods are supported:

-   ``curve25519-sha256@libssh.org``
-   ``diffie-hellman-group1-sha1``
-   ``diffie-hellman-group14-sha1``
-   ``ecdh-sha2-nistp256``
-   ``ecdh-sha2-nistp384``
-   ``ecdh-sha2-nistp521``

The PHP ``hash`` extension must be installed for
``curve25519-sha256@libssh.org`` and the ``ecdsa-sha2-*`` family
of algorithms to work properly.
Also, elliptic curve points encoded using point compression
are **not** accepted or generated.


Encryption algorithms
~~~~~~~~~~~~~~~~~~~~~

The following encryption algorithms are supported:

-   ``3des-cbc``
-   ``3des-ctr``
-   ``aes128-cbc``
-   ``aes128-ctr``
-   ``aes128-gcm@openssh.com``
-   ``aes192-cbc``
-   ``aes192-ctr``
-   ``aes256-cbc``
-   ``aes256-ctr``
-   ``aes256-gcm@openssh.com``
-   ``arcfour``
-   ``arcfour128``
-   ``arcfour256``
-   ``blowfish-cbc``
-   ``blowfish-ctr``
-   ``cast128-cbc``
-   ``cast128-ctr``
-   ``chacha20-poly1305@openssh.com``
-   ``idea-cbc``
-   ``idea-ctr``
-   ``none``
-   ``rijndael-cbc@lysator.liu.se`` (as an alias for ``aes256-cbc``)
-   ``serpent128-cbc``
-   ``serpent192-cbc``
-   ``serpent256-cbc``
-   ``serpent128-ctr``
-   ``serpent192-ctr``
-   ``serpent256-ctr``
-   ``twofish-cbc``
-   ``twofish128-cbc``
-   ``twofish192-cbc``
-   ``twofish256-cbc``
-   ``twofish128-ctr``
-   ``twofish192-ctr``
-   ``twofish256-ctr``

MAC algorithms
~~~~~~~~~~~~~~

The following MAC algorithms are supported:

-   ``hmac-md5``
-   ``hmac-md5-etm@openssh.com``
-   ``hmac-md5-96``
-   ``hmac-md5-96-etm@openssh.com``
-   ``hmac-ripemd160``
-   ``hmac-ripemd160@openssh.com`` (as an alias for ``hmac-ripemd160``)
-   ``hmac-ripemd160-etm@openssh.com``
-   ``hmac-sha1``
-   ``hmac-sha1-etm@openssh.com``
-   ``hmac-sha1-96``
-   ``hmac-sha1-96-etm@openssh.com``
-   ``hmac-sha2-256``
-   ``hmac-sha2-256-etm@openssh.com``
-   ``hmac-sha2-512``
-   ``hmac-sha2-512-etm@openssh.com``
-   ``none``
-   ``ripemd160`` (as an alias for ``hmac-ripemd160``)
-   ``umac-64@openssh.com``
-   ``umac-64-etm@openssh.com``
-   ``umac-128@openssh.com``
-   ``umac-128-etm@openssh.com``

All these algorithms except for the ``umac-*`` family require
the PHP ``hash`` extension in order to work properly.

Public key algorithms
~~~~~~~~~~~~~~~~~~~~~

The following public key algorithms are supported:

-   ``ecdsa-sha2-nistp256``
-   ``ecdsa-sha2-nistp384``
-   ``ecdsa-sha2-nistp521``
-   ``ssh-dss``
-   ``ssh-ed25519``
-   ``ssh-rsa``

The PHP ``hash`` extension must be installed for the ``ssh-ed25519``
and ``ecdsa-sha2-*`` family of algorithms to work properly.
Also, elliptic curve points encoded using point compression
are **not** accepted or generated.

Compression algorithms
~~~~~~~~~~~~~~~~~~~~~~

The following compression algorithms are supported:

-   ``none``
-   ``zlib``
-   ``zlib@openssh.com``

The PHP ``http`` extension must be installed for the ``zlib`` and
``zlib@openssh.com`` algorithms to work properly.


Integration
-----------

pssht is mainly intended to be used as an embedded SSH server for PHP applications.
By default, only the bare structure for an SSH server is provided.
The application using pssht is responsible for adding it's own logic on top
of this structure.


Contributions
-------------

Want to contribute back to the project?

-   `Fork the code <https://github.com/fpoirotte/pssht/fork_select>`_
    to your own account.
-   Create a new branch.
-   Hack around.
-   Create a pull request with your changes.


License
-------

The MIT License (MIT)

Copyright (c) 2014 François Poirotte

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


..  _`draft-miller-secsh-umac-01`:
    https://tools.ietf.org/html/draft-miller-secsh-umac-01

..  _`draft-miller-secsh-compression-delayed-00`:
    https://tools.ietf.org/html/draft-miller-secsh-compression-delayed-00

..  _`OpenSSH PROTOCOL`:
    http://cvsweb.openbsd.org/cgi-bin/cvsweb/src/usr.bin/ssh/PROTOCOL?rev=HEAD

..  _`OpenSSH private key format`:
    http://cvsweb.openbsd.org/cgi-bin/cvsweb/src/usr.bin/ssh/PROTOCOL.key?rev=HEAD

..  _`ChaCha20-Poly1305`:
    http://cvsweb.openbsd.org/cgi-bin/cvsweb/src/usr.bin/ssh/PROTOCOL.chacha20poly1305?rev=HEAD

..  _`Ed25519 curve`:
    http://ed25519.cr.yp.to/software.html

..  _`Curve25519 curve`:
    http://git.libssh.org/projects/libssh.git/plain/doc/curve25519-sha256@libssh.org.txt

..  |[ ]| unicode:: U+2610 .. ballot box
..  |[x]| unicode:: U+2611 .. ballot box with check
..  |---| unicode:: U+2014 .. em dash
    :trim:

