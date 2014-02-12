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


Installation
------------

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


Configuration
-------------

pssht uses the Dependency Injection component from the Symfony2 framework
for its configuration. Have a look at the default
`pssht.xml <https://github.com/fpoirotte/pssht/blob/master/pssht.xml>`_
for ways to configure pssht.


Extensions
----------

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

