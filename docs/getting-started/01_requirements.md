# Requirements for Fortress

<!-- TOC -->
* [PHP](#php)
    * [Non-default PHP extensions](#non-default-php-extensions)
* [WordPress](#wordpress)
* [Database](#database)
    * [MySQL/MariaDB or similar](#mysqlmariadb-or-similar)
    * [Storage engine](#storage-engine)
    * [Client driver](#client-driver)
        * [Check mysqli driver for PHP-FPM/webserver](#check-mysqli-driver-for-php-fpmwebserver)
        * [Check mysqli driver for PHP-CLI](#check-mysqli-driver-for-php-cli)
* [Server-side IP detection and forwarding](#server-side-ip-detection-and-forwarding)
<!-- TOC -->

---

## PHP

- Version `^7.4` or `^8.0` (Version 7.4, or any version of 8.0 or higher)

### Non-default PHP extensions

- `mbstring`
- `intl`

The above PHP extensions are all "highly recommended" in
the [WordPress hosting handbook](https://make.wordpress.org/hosting/handbook/server-environment/#php-extensions).
They are pre-installed in most hosting environments.

## WordPress

- Version `^6.0` (Version 6.0 or higher)
- WP-CLI `^2.6.0` (Version 2.6.0 or higher) - While older versions likely work, all tests are conducted against version
  2.6.0.

Check your WP-CLI version with the `wp --version` command:

```console
$ wp --version

WP-CLI 2.6.0
```

## Database

### MySQL/MariaDB or similar

As WordPress Core, Fortress works with any MySQL/MariaDB compatible database.

The only requirement is that the database can create `DATETIME` columns with a default of `DEFAULT (UTC_TIMESTAMP)`.

This means:

- MySQL: `^8.0.13` (Version 8.0.13 or higher)
- MariaDB: `^10.2.0` (Version 10.2.0 or higher)

Check your MySQL/MariaDB version in the WordPress Site Health page at
`/wp-admin/site-health.php?tab=debug` under the `"Database > Server version"` section.

### Storage engine

- The `(wp_)users` table must use the `InnoDB` storage engine, not the outdated and slow `MyISAM` engine.<br>
  Fortress requires foreign keys and row-level locking for some of its functionality.<br>
  You can [convert MySIAM tables to InnoDB](https://gridpane.com/kb/converting-myisam-to-innodb/).

### Client driver

The `mysqli` extension must use the default MySQL native driver (`mysqlnd`) for PHP, **not** the
legacy `libmysqlclient`.

`mysqlnd` is
the [officially recommended driver by PHP since version 5.3](https://www.php.net/manual/en/mysqlnd.overview.php),
providing many performance and memory usage improvements over the legacy `libmysqlclient`.

However, we've come across some poorly configured cPanel setups that still use `libmysqlclient`.

You can verify easily which driver is being used:

#### Check mysqli driver for PHP-FPM/webserver

1. Go to `/wp-admin/site-health.php?tab=debug`.
2. Check the "Database" dropdown for the `Client version` row.
3. It should show something like `mysqlnd 7.4.33`.

The version number may vary, but it should always start with `mysqlnd`.

#### Check mysqli driver for PHP-CLI

Sometimes, PHP-CLI uses a different PHP version than the webserver,
and thus, `WP-CLI` commands may use a different driver than the webserver.

Check your PHP-CLI driver with the following command:

```shell
php -r 'printf("%s", mysqli_get_client_info());'
```

The output should be similar to:

```console
mysqlnd 8.1.2-1ubuntu2.18
```

The version number may vary, but it should always be `mysqlnd`.

## Server-side IP detection and forwarding

Fortress **only** uses the `REMOTE_ADDR` server variable to detect the client's IP address;
it's the [only](https://snicco.io/blog/how-to-safely-get-the-ip-address-in-a-wordpress-plugin)
secure [way](https://snicco.io/vulnerability-disclosure/classification/ip-spoofing).

If you are using any type of reverse proxy, load balancer, or fronted CDN, ensure that the real IP address is
correctly restored at the **webserver level**.

For example, if you're using Nginx and CloudFlare,
use the [`ngx_http_realip_module`](https://nginx.org/en/docs/http/ngx_http_realip_module.html) module with
the following configuration:

```nginx
real_ip_header CF-Connecting-IP;
set_real_ip_from 173.245.48.0/20;
# repeat for all Cloudflare IPs listed at:
# IPv4 list https://www.cloudflare.com/ips-v4
# IPv6 list https://www.cloudflare.com/ips-v6
```

Note: This is in no way exclusive to Fortress;
[WordPress Core will not function correctly without proper IP rewriting](https://make.wordpress.org/core/handbook/contribute/design-decisions/#no-support-for-forwarding-headers-for-https-or-ip-addresses).

---

Next: [Installation](02_installation.md)