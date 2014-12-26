pssht
=====

pssht is a library that provides an SSH server that can be embedded
in other applications.

What we're aiming for:

*   Clean code (eg. PSR-2 & PSR-4 compliance, tests, etc.)
*   Extensibility
*   Compatibility with as much SSH clients as possible

What we're not specifically aiming for, but still interested in:

*   Completeness (like support for TCP port forwarding, TUN/TAP tunneling,
    the scp/sftp subsystems, etc.)
*   Strong security (peer reviews, security audits, etc.)


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
see `this reddit page <http://www.reddit.com/r/lolphp/comments/1yvm6v/php_can_do_anything_what_about_some_ssh_mtgox>`_
which relates part of the story of a project similar to pssht by MtGox's CEO.

In no event shall the authors of pssht be liable for anything that happens
while using this library. See also the `license`_ for the full disclaimer.


Installation & Usage
--------------------

Download the `composer.phar <https://getcomposer.org/composer.phar>`_
executable or use the installer.

..  sourcecode:: bash

    $ curl -sS https://getcomposer.org/installer | php

Create a ``composer.json`` that requires pssht.

..  sourcecode:: json

    {
        "require": {
            "fpoirotte/pssht": "dev-master"
        }
    }

Run Composer.

..  sourcecode:: bash

    $ php composer.phar install

Run the server.

..  sourcecode:: bash

    $ php bin/pssht


Configuration
-------------

pssht uses the Dependency Injection component from the Symfony2 framework
for its configuration. Have a look at the default `pssht.xml
<https://github.com/fpoirotte/pssht/blob/master/pssht.xml>`_
for ways to configure pssht.


Compatibility
-------------

pssht supports the mechanisms and algorithms defined in the following
documents for compatibility with other Secure Shell implementations:

-   `RFC 4250`_ |---| SSH Protocol Assigned Numbers
-   `RFC 4251`_ |---| SSH Protocol Architecture
-   `RFC 4252`_ |---| SSH Authentication Protocol
-   `RFC 4253`_ |---| SSH Transport Layer Protocol
-   `RFC 4254`_ |---| SSH Connection Protocol
-   `RFC 4344`_ |---| SSH Transport Layer Encryption Modes
-   `RFC 4345`_ |---| Improved Arcfour Modes for the SSH Transport Layer Protocol
-   `RFC 4462`_ |---| SSH Public Key File Format
-   `RFC 5647`_ |---| AES Galois Counter Mode for the SSH Transport Layer Protocol
-   `RFC 5656`_ |---| Elliptic Curve Algorithm Integration in SSH
-   `RFC 6668`_ |---| SHA-2 Data Integrity Algorithms
-   `draft-miller-secsh-umac-01`_ |---| UMAC in the SSH Transport Layer Protocol
-   `draft-miller-secsh-compression-delayed-00`_ |---| Delayed compression until after authentication
-   `OpenSSH PROTOCOL`_ |---| Various OpenSSH extensions to the SSH protocol
-   `OpenSSH private key format`_ |---| Specification for OpenSSH's private key format
-   `Ed25519 curve`_ |---| Twisted Edwards Curve 2\*\*255-19

The rest of this section describes precisely which algorithms and features
are supported.

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

-   ``diffie-hellman-group1-sha1``
-   ``diffie-hellman-group14-sha1``
-   ``ecdh-sha2-nistp256``
-   ``ecdh-sha2-nistp384``
-   ``ecdh-sha2-nistp521``

The PHP ``hash`` extension must be installed for the ``ecdsa-sha2-*``
family of algorithms to work properly. Also, elliptic curve points
encoded using point compression are **not** accepted or generated.


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
-   ``ripemd160@openssh.com`` (as an alias for ``hmac-ripemd160``)
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


..  _`RFC 4250`:
    https://tools.ietf.org/html/rfc4250

..  _`RFC 4251`:
    https://tools.ietf.org/html/rfc4251

..  _`RFC 4252`:
    https://tools.ietf.org/html/rfc4252

..  _`RFC 4253`:
    https://tools.ietf.org/html/rfc4253

..  _`RFC 4254`:
    https://tools.ietf.org/html/rfc4254

..  _`RFC 4344`:
    https://tools.ietf.org/html/rfc4344

..  _`RFC 4345`:
    https://tools.ietf.org/html/rfc4345

..  _`RFC 4462`:
    https://tools.ietf.org/html/rfc4462

..  _`RFC 5657`:
    https://tools.ietf.org/html/rfc5657

..  _`RFC 5656`:
    https://tools.ietf.org/html/rfc5656

..  _`RFC 6668`:
    https://tools.ietf.org/html/rfc6668

..  _`draft-miller-secsh-umac-01`:
    https://tools.ietf.org/html/draft-miller-secsh-umac-01

..  _`draft-miller-secsh-compression-delayed-00`:
    https://tools.ietf.org/html/draft-miller-secsh-compression-delayed-00

..  _`OpenSSH PROTOCOL`:
    http://cvsweb.openbsd.org/cgi-bin/cvsweb/src/usr.bin/ssh/PROTOCOL?rev=HEAD

..  _`OpenSSH private key format`:
    http://cvsweb.openbsd.org/cgi-bin/cvsweb/src/usr.bin/ssh/PROTOCOL.key?rev=HEAD

..  _`Ed25519 curve`:
    http://ed25519.cr.yp.to/software.html

..  |---| unicode:: U+02014 .. em dash
    :trim:

