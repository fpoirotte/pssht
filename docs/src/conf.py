# -*- coding: utf-8 -*-

import os
import sys
import glob
import shutil
from datetime import datetime
from subprocess import call, Popen, PIPE

try:
    import simplejson as json
except ImportError:
    import json

def prepare(globs, locs):
    # Where are we?
    cwd = os.getcwd()
    root = os.path.abspath(os.path.join(cwd, '..', '..'))

    git = Popen('which git 2> %s' % os.devnull, shell=True, stdout=PIPE
                ).stdout.read().strip()
    doxygen = Popen('which doxygen 2> %s' % os.devnull, shell=True, stdout=PIPE
                ).stdout.read().strip()
    pybabel = Popen('which pybabel 2> %s' % os.devnull, shell=True, stdout=PIPE
                ).stdout.read().strip()

    print "git version:",
    sys.stdout.flush()
    call([git, '--version'])

    print "doxygen version:",
    sys.stdout.flush()
    call([doxygen, '--version'])

    print "pybabel version:",
    sys.stdout.flush()
    call([pybabel, '--version'])

    print "environment:", repr(os.environ)

    print "Running from %s..." % (root, )
    os.chdir(root)

    # Figure several configuration values from git.
    origin = Popen([git, 'config', '--local', 'remote.origin.url'],
                   stdout=PIPE).stdout.read().strip()
    git_tag = Popen([git, 'describe', '--tags', '--exact', '--first-parent'],
                    stdout=PIPE).communicate()[0].strip()
    git_hash = Popen([git, 'rev-parse', 'HEAD'],
                    stdout=PIPE).communicate()[0].strip()
    gh_project = ('/'.join(origin.split('/')[-2:])).rpartition(':')[2]
    if gh_project.endswith('.git'):
        gh_project = gh_project[:-4]
    project = gh_project.rpartition('/')[2]
    os.environ['SPHINX_PROJECT'] = project
    if git_tag:
        os.environ['SPHINX_VERSION'] = os.environ['SPHINX_RELEASE'] = git_tag
    else:
        os.environ['SPHINX_VERSION'] = os.environ['SPHINX_RELEASE'] = 'latest'
        locs['tags'].add('devel')

    # Clone or update dependencies
    buildenv = os.path.join(root, 'vendor', 'erebot', 'buildenv')
    natives = os.path.join(root, 'vendor', 'fpoirotte', 'natives4doxygen')
    for repository, path in (
        ('git://github.com/Erebot/Buildenv.git', buildenv),
        ('git://github.com/fpoirotte/PHPNatives4Doxygen', natives),
    ):
        if not os.path.isdir(path):
            os.makedirs(path)
            print "Cloning %s into %s..." % (repository, path)
            call([git, 'clone', repository, path])
        elif os.path.isdir(os.path.join(path, '.git')):
            os.chdir(path)
            print "Updating clone of %s in %s..." % (repository, path)
            call([git, 'checkout', 'master'])
            call([git, 'pull'])
            os.chdir(root)

    composer = json.load(open(os.path.join(root, 'composer.json'), 'r'))

    # Run doxygen
    call([doxygen, os.path.join(root, 'Doxyfile')], env={
        'COMPONENT_NAME': os.environ['SPHINX_PROJECT'],
        'COMPONENT_VERSION': os.environ['SPHINX_VERSION'],
        'COMPONENT_BRIEF': composer.get('description', ''),
    })

    # Remove extra files/folders.
    try:
        shutil.rmtree(os.path.join(root, 'build'))
    except OSError:
        pass
    shutil.move(
        os.path.join(root, 'docs', 'api', 'html'),
        os.path.join(root, 'build', 'apidoc'),
    )

    # Compile translation catalogs.
    for po in glob.iglob(os.path.join(root, 'docs', 'i18n', '*', 'LC_MESSAGES', '*.po')):
        mo = po[:-3] + '.mo'
        call([pybabel, 'compile', '-f', '--statistics', '-i', po, '-o', mo])

    # Load the real Sphinx configuration file.
    os.chdir(cwd)
    real_conf = os.path.join(buildenv, 'sphinx', 'conf.py')
    print "Including real configuration file (%s)..." % (real_conf, )
    execfile(real_conf, globs, locs)

    # Patch configuration afterwards.
    locs['copyright'] = u'2014-%d, François Poirotte. All rights reserved' % \
            datetime.now().year
    if 'html_extra_path' not in locs:
        locs['html_extra_path'] = []
    locs['html_extra_path'].append(os.path.join(root, 'build'))
    locs['html_theme'] = 'haiku'
    if 'locale_dirs' not in locs:
        locs['locale_dirs'] = []
    locs['locale_dirs'].insert(0, os.path.join(root, 'docs', 'i18n'))
    if 'rst_prolog' not in locs:
        locs['rst_prolog'] = ''
    locs['rst_prolog'] += '\n    .. _`this_commit`: https://github.com/%s/commit/%s\n' % (
        gh_project,
        git_hash,
    )

    globs['RTD_NEW_THEME'] = False
    globs['RTD_OLD_THEME'] = False


prepare(globals(), locals())
