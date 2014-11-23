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
    scp/sftp subsystems, etc.)


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
for its configuration. Have a look at the default
`pssht.xml <https://github.com/fpoirotte/pssht/blob/master/pssht.xml>`_
for ways to configure pssht.


Compatibility
-------------

pssht supports the following features for compatibility with other
Secure Shell implementations:

-   `RFC 4250 <https://tools.ietf.org/html/rfc4250>`_
    |---| SSH Protocol Assigned Numbers

-   `RFC 4251 <https://tools.ietf.org/html/rfc4251>`_
    |---| SSH Protocol Architecture

-   `RFC 4252 <https://tools.ietf.org/html/rfc4252>`_
    |---| SSH Authentication Protocol

-   `RFC 4253 <https://tools.ietf.org/html/rfc4253>`_
    |---| SSH Transport Layer Protocol

-   `RFC 4254 <https://tools.ietf.org/html/rfc4254>`_
    |---| SSH Connection Protocol

-   `RFC 4344 <https://tools.ietf.org/html/rfc4344>`_
    |---| SSH Transport Layer Encryption Modes

-   `RFC 4345 <https://tools.ietf.org/html/rfc4345>`_
    |---| Improved Arcfour Modes for the SSH Transport Layer Protocol

-   `RFC 5656 <https://tools.ietf.org/html/rfc5656>`_
    |---| Elliptic Curve Algorithm Integration in SSH [1]_

-   `RFC 6668 <https://tools.ietf.org/html/rfc6668>`_
    |---| SHA-2 Data Integrity Algorithms

-   `zlib@openssh.com <https://tools.ietf.org/html/draft-miller-secsh-compression-delayed-00>`_
    |---| Delayed compression until after authentication


..  [1] Only the ``ecdsa-sha2-nistp256``, ``ecdsa-sha2-nistp384``
    and ``ecdsa-sha2-nistp521`` curves over ``GF(p)`` are supported.
    Elliptic curve points encoded using point compression
    are **not** accepted or generated.


Integration
-----------

pssht is mainly intended for use as an embedded SSH server for PHP applications.
By default, only the bare structure for an SSH server is provided.
The application using pssht is responsible for adding it's own logic on top
of this structure.


Contributions
-------------

Want to contribute back to the project?

-   `Fork the code <https://github.com/Erebot/Erebot/fork_select>`_
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

..  |---| unicode:: U+02014 .. em dash
    :trim:

