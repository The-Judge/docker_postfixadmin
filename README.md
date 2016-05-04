# About this image

![postfixadmin](http://i.imgur.com/UCtvKHR.png "postfixadmin")

Postfix Admin is a web based interface used to manage mailboxes, virtual domains and aliases. It also features support for vacation/out-of-the-office messages.

This work is heavily based on [hardware/postfixadmin](https://github.com/hardware/postfixadmin). Most is 1:1 identically and most of the credits for this image go to [hardware](https://github.com/hardware), exceptionally the following differences (as of the time of this writing):

- There was no scripting hook in the startup process which made it possible to change anything in the startup process. This image supports that.
- There was no way to keep local config for Postfix Admin during container restarts. This image offers a way to save the config during restarts.
- Postfix Admin source was taken from a ``tar.gz`` file and thus hard coded to a specific version. This now comes from SVN, is ``trunk`` by default and can be changed to a [tag](http://svn.code.sf.net/p/postfixadmin/code/tags/) you desire.
- [Supervisor](http://supervisord.org/) is used in the original image, but configured and started in a way, which breaks some of it's features (like using ``supervisorctl``). This is fixed in this image.
- This image supports both, MySQL and PostgreSQL databases.

### Requirements

- Docker 1.0 or higher
- Separate MySQL or PostgreSQL server

### How to start a container

You can start a container of this image with the following command:

```
docker run -d \
  --name postfixadmin
  -p 80:80 \
  -e DBPASS=xxxxxxxx \
  -h mail.domain.tld \
  derjudge/postfixadmin
```

- ``--name postfixadmin``: "*postfixadmin*" can be anything you like to make it easy for you to identify the container in Docker's list of containers and to refer to it in your setups and scripts.
- ``-p 80:80`` opens port 80 (default for HTTP) on all IPs of your Docker-Host. If that socket is already in use, you can limit this to a specific IP only; refer to [Docker's official docs](https://docs.docker.com/engine/reference/run/#expose-incoming-ports) for more detail. If you plan to add a reverse proxy to your setup, you most probably want to not use ``-p`` at all.
- ``-e DBPASS=xxxxxxxx``: "*xxxxxxxx*" must be replaced by the password for the DB-user, set by the variable ``DBUSER`` (defaults to ``postfix``).
- ``-h mail.domain.tld``: This should be set to the FQDN of your Postfix Server.

**Note:** This image does not ship with a RDBS (MySQL or PostgreSQL). You will have to setup one on your own and make sure both, this container and your Postfix/IMAP-Daemons have access to.

There are ways to use docker for this; read about the [linking system](https://docs.docker.com/engine/userguide/networking/default_network/dockerlinks/#connect-with-the-linking-system) of Docker.

#### Setup

After you gave the container a few seconds to start (usually <10), you should be able to navigate your favorite webbrowser to ``http://ip/setup.php`` (replace *ip* with the FQDN/ip of your server).

The automatic setup script then guides you through the rest of the process

### List of environment variables

- **GID** = postfixadmin user id (*optional*, default: 991)
- **UID** = postfixadmin group id (*optional*, default: 991)
- **DBS** = Database system to use (*optional*, default: mysqli)
- **DBHOST** = Database instance ip/hostname (*optional*, default: dbhost)
- **DBUSER** = Database username (*optional*, default: postfix)
- **DBNAME** = Database name (*optional*, default: postfix)
- **DBPASS** = Database password (**required**)
- **SETUPPASS** = Postfix Admin setup password (*optional*)
- **VERSION** = Postfix Admin version to use. (*optional*, default: trunk)

### The special folder /extra

When you restart a Docker container, you usually delete it and create a new one afterwards. Docker offers a way to keep your data with the concept of VOLUMES, but I, personally, don't like VOLUMES. You can use them if you like, but I won't describe how to do so in here.
Since I'm more a fan for Bind mounts of directories from the Host's filesystem, this will be the way, I describe with the following.
The basic idea is to provide a persistent data store, mounted at ``/extra`` within the container to make some additional features of this image work (like persistent configuration and startup hook)

There's an commandline option for ``docker run``, which maps (mounts) any volume to a path inside a docker container: ``-v``. Not only this can map Docker VOLUMES from other Docker containers, but also it can make directories from the Docker Host available inside any container.
Alternatively, you can also use Docker VOLUMES to provide a persistent data store mounted at ``/extra``.

First, create an empty folder anywhere on your docker host. For example, lets use ``/home/foobar/folder``:

```
#> mkdir -p /home/foobar/folder
```

Finally, you have to bind mount this folder to your Postfix Admin container by adding the following to the command line of your ``docker run`` command:

```
-v /home/foobar/folder:/extra:ro
```

This makes everything inside the folder ``/home/foobar/folder`` of your Docker host to be available at ``/extra`` inside your container.
In this example, the optional parameter ``:ro`` was added, which makes the mount to be read-only inside your container. This is an additional security measure. If you plan to copy files (like your ``/postfixadmin/config.local.php`` file) into ``/extra``, you should remove that parameter or change it to be ``:rw``, which both make that mount Read/Write.

### Startup hook

This can be used to enable the startup hook - functionality this image provides.
Whenever there is a file named ``/extra/init``, it is assumed to be a shell script and gets executed just before the daemons are started.

**Note:** You have to make sure, that script does not get stuck by starting something in the foreground, for example, since this will prevent the remaining startup process from being executed:

```
#> touch /home/foobar/folder/init
#> chmod +x /home/foobar/folder/init
```

You can enter any commands into that file you desire; they will be executed just before the daemons are started. For example, you could write the following into that file:

```
rm -f /postfixadmin/CHANGELOG.TXT
```

To have the file ``/postfixadmin/CHANGELOG.TXT`` deleted before the webserver makes it available.

**Note:** The shebang line (``#! ...``) should point either to ``/bin/ash`` or ``/bin/sh``; ``bash`` is not available. Therefore, please stick to compatible syntax for these shells to make sure your script works as expected.

### Preserve Postfix Admin configuration

Postfix Admin has two configuration files:

- ``config.inc.php``: This is the **factory default configuration** file, shipped with Postfix Admin. This should never be changed, since it's contents may be overwritten at any time and your local changes are lost.
- ``config.local.php``: This file is **for all your local changes to the factory default config**. When there are two concurrent settings in ``config.inc.php`` and ``config.local.php``, the settings defined in here takes precedence. Also, this file is not shipped with Postfix Admin code and therefore should never get overwritten by these.

When you first start a container and there is no file named ``/extra/config.local.php`` available, ``/postfixadmin/config.local.php`` is created by what you have set as environment variables.
If ``/extra/config.local.php`` is available, it will be copied to ``/postfixadmin/config.local.php`` whenever the container starts (even if there is just nonsense in there!).

This is by design and the way the configuration preservation works: During setup, you will make those changes to ``/postfixadmin/config.local.php`` as Postfix Admin setup script suggests or you want to take over from ``/postfixadmin/config.inc.php`` with your own values. When everything works as you like, you just copy ``/postfixadmin/config.inc.php`` to ``/extra/config.local.php``. Next time you restart your container, it will be restored.

### Using PostgreSQL instead of MySQL

By default, the Postfix Admin configuration option ``$CONF['database_type']`` is set to the content of the variable ``$DBS``, which is set to ``mysqli`` by default. This results in:

```
$CONF['database_type'] = 'mysqli';
```

When you add this to the Docker commandline:

```
-e DBS=pgsql
```

you set this variable to ``pgsql`` instead, which results in:

```
$CONF['database_type'] = 'pgsql';
```

and uses that PostgreSQL module for DB connections instead.

### Using a different version of Postfix Admin

This image uses Subversion to checkout (download) a copy of Postfix Admin when it is build. It uses the [trunk](http://svn.code.sf.net/p/postfixadmin/code/trunk) for this, which represents the latest version of the code available.

If you want to use another version, you can do so by defining the variable ``$VERSION`` on your Docker commandline, adding:

```
-e VERSION=postfixadmin-x.yy
```

This switches to the specified tag (Subversion) before starting the daemons.
You can use every [tag in the Postfix Admin Subversion tree](http://svn.code.sf.net/p/postfixadmin/code/tags/). For example, if you would like to use version ``2.92`` of Postfix Admin, you would add the following to your Docker commandline:

```
-e VERSION=postfixadmin-2.92
```

### Docker-compose

The following is an example for docker-compose, using MariaDB as DBS:

``Docker-compose.yml``:

```
postfixadmin:
  image: derjudge/postfixadmin
  container_name: postfixadmin
  domainname: domain.tld
  hostname: mail
  links:
    - mariadb:dbhost
  ports:
    - "80:80"
  environment:
    - DBHOST=dbhost
    - DBUSER=postfix
    - DBNAME=postfix
    - DBPASS=xxxxxxx

mariadb:
  image: mariadb:10.1
  volumes:
    - /docker/mysql/db:/var/lib/mysql
  environment:
    - MYSQL_ROOT_PASSWORD=xxxx
    - MYSQL_DATABASE=postfix
    - MYSQL_USER=postfix
    - MYSQL_PASSWORD=xxxx
```

Startup:

```
docker-compose up -d
```