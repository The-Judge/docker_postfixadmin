# About this image

![postfixadmin](http://i.imgur.com/UCtvKHR.png "postfixadmin")

Postfix Admin is a web based interface used to manage mailboxes, virtual domains and aliases. It also features support for vacation/out-of-the-office messages.

This work is based on [hardware/postfixadmin](https://github.com/hardware/postfixadmin). Most is 1:1 identically, with the following differences (as of the time of this writing):

- There was no scripting hook in the startup process which made it possible to change anything in the startup process.
- There was no way to keep local config for Postfix Admin during container restarts.
- Postfix Admin source was taken from a ``tar.gz`` file and thus hard coded to a specific version. This now comes from SVN, is ``trunk`` by default and can be changed to a [tag](http://svn.code.sf.net/p/postfixadmin/code/tags/) you desire.
- [Supervisor](http://supervisord.org/) is used in the original image, but configured and started in a way, which breaks some of it's features (like using ``supervisorctl``). This is fixed in this image.
- This image supports both, MySQL and PostgreSQL databases.

### Requirements

- Docker 1.0 or higher
- Separate MySQL or PostgreSQL server

### How to use

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


#### Setup

After you gave the container a few seconds to start (usually <10), you should be able to navigate your favorite webbrowser to ``http://ip/setup.php`` (replace *ip* with the FQDN/ip of your server).

### Environment variables

- **GID** = postfixadmin user id (*optional*, default: 991)
- **UID** = postfixadmin group id (*optional*, default: 991)
- **DBS** = Database system to use (*optional*, default: mysqli)
- **DBHOST** = Database instance ip/hostname (*optional*, default: dbhost)
- **DBUSER** = Database username (*optional*, default: postfix)
- **DBNAME** = Database name (*optional*, default: postfix)
- **DBPASS** = Database password (**required**)

### Startup hook

TODO: Doku

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