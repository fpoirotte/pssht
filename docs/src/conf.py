# -*- coding: utf-8 -*-

import os
import stat
from os.path import join, abspath
from subprocess import call

def prepare(globs, locs):
    # RTD defaults the current working directory to where conf.py resides.
    # In our case, that means <root>/docs/src/.
    cwd = os.getcwd()
    root = abspath(join(cwd, '..', '..'))
    os.chdir(root)

    # Download the PHP binary & composer.phar if necessary
    base = 'https://github.com/Erebot/Buildenv/releases/download/1.4.0'
    for f in ('php', 'composer.phar'):
        call(['curl', '-L', '-z', f, '-o', f, '%s/%s' % (base, f)])

    # Make sure the PHP interpreter is executable
    os.chmod('./php', stat.S_IRUSR | stat.S_IWUSR | stat.S_IXUSR)

    # Call composer to download/update dependencies as necessary
    os.environ['COMPOSER_CACHE_DIR'] = './cache'
    call(['./php', 'composer.phar', 'update', '-n', '--ignore-platform-reqs',
          '--no-progress'], env=os.environ)

    # Load the second-stage configuration file.
    os.chdir(cwd)
    conf = join(root, 'vendor', 'erebot', 'buildenv', 'sphinx', 'rtd.py')
    print "Including the second configuration file (%s)..." % (conf, )
    execfile(conf, globs, locs)

prepare(globals(), locals())
