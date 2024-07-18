# Secret management

<!-- TOC -->

* [Overview](#overview)
* [Different ways Fortress can read secrets](#different-ways-fortress-can-read-secrets)
    * [Env variables that point to a file](#env-variables-that-point-to-a-file)
    * [ENV variables that are values](#env-variables-that-are-values)
    * [PHP constants](#php-constants)
* [Functional requirements](#functional-requirements)
    * [Secrets must never be changed manually, or by accident](#secrets-must-never-be-changed-manually-or-by-accident)
    * [Secrets must available during web and cli requests](#secrets-must-be-available-during-web-and-cli-requests)
    * [Secrets might need to stay in sync between staging and production](#secrets-might-need-to-stay-in-sync-between-staging-and-production)
    * [Accessing secrets must be fast](#accessing-secrets-must-be-fast)
* [Where not to store secrets](#where-not-to-store-secrets)
    * [Don't store secrets in the database](#dont-store-secrets-in-the-database)
    * [Don't store secrets in wp-config.php](#dont-store-secrets-in-wp-configphp)
* [The best ways to store secrets](#the-best-ways-to-store-secrets)
    * [1. Storing secrets in a KMS with instance restricted access](#1-storing-secrets-in-a-kms-with-instance-restricted-access)
    * [2. Storing secrets in an external KMS](#2-storing-secrets-in-an-external-kms)
    * [3.1 PHP constants stored in a dedicated file outside the web root](#31-php-constants-stored-in-a-dedicated-file-outside-the-web-root)
    * [3.1 ENV variables that contain a file path](#31-env-variables-that-contain-a-file-path)
    * [4. ENV variables that contain the secret value](#4-env-variables-that-contain-the-secret-value)
    * [Which method to choose?](#which-method-to-choose)
* [Generating new secrets](#generating-new-secrets)

<!-- TOC -->

---

## Overview

> 🚨 This is an advanced topic that requires advanced understanding of secret management.
> <br>
> If this is not you, don't worry—The default installation guide of Fortress will work just fine!

Fortress requires multiple cryptographic secrets for a lot of its functionality, such as:

- [Vaults and Pillars](../../modules/vaults_and_pillars)
- [Password Security](../../modules/password)
- [Magic Links](../../modules/auth/magic_login_links.md)
- [2FA/TOTP](../../modules/auth/2fa_totp.md)

Fortress can generate secrets for you, but you have to determine the best way to store them securely
and provide them to Fortress at runtime.

This is admittedly a complex topic.

This guide will discuss the different ways Fortress can read secrets, define the functional requirements, and then
discuss the pros and cons of each method.

## Different ways Fortress can read secrets

### Env variables that point to a file

If a secret is found in `$_SERVER` **and** if the value of the variable is a file path,
Fortress will read the secret from the file.

This method plays very well with [Docker secrets](https://docs.docker.com/engine/swarm/secrets/)

### ENV variables that are values

If an env variable is found in `$_SERVER` and the value is not a file path, Fortress will use the value as the secret.

### PHP constants

Lastly, Fortress will check if a PHP constant is defined with the name of the secret.

This method is ultimately a catch-all, since you can define a PHP constant in any way you like.

However, the constant must be defined **before** Fortress is booted by the
[Fortress loader](../02_installation.md#create-a-fortress-loader-and-activate-fortress).

The default [installation guide](../02_installation.md#create-fortress-secrets) of Fortress uses this
method to define all Fortress secrets in a `secrets.php` file that is included
in the default Fortress loader before booting Fortress itself.

## Functional requirements

### Secrets must never be changed manually, or by accident

If you change your Fortress secrets manually, none of your encrypted
data can be decrypted anymore, and unless you have backups, the data is lost and cannot be recovered.

### Secrets must be available during web and CLI requests

Your web requests, as well as your WP-CLI requests, must have access to the same set of secrets.

### Secrets might need to stay in sync between staging and production

This topic is discussed in more detail in the [staging sites guide](staging-sites.md).

If you push data from production to staging and back from staging to production, you must ensure that
the same set of secrets is used in both environments.

### Accessing secrets must be fast

Fortress secrets are needed for almost every request, so
the method you choose to store secrets cannot involve slow operations like network requests
without local caching.

## Where not to store secrets

### Don't store secrets in the database

**Never** do this, if you store any encryption keys in the database where the encrypted
data is stored, you have effectively defeated the purpose of encryption.

Sadly, this is often the default in the WordPress ecosystem.

### Don't store secrets in wp-config.php

Storing secrets hardcoded in the `wp-config.php` file
is often recommended as a secure way to store secrets in the WordPress ecosystem.

This is **bad idea** for many reasons:

1. Your encryption keys are now most likely included in backups.
   Your backups now can contain the encrypted data and the keys to decrypt it, which
   means that you reduced the security of your encrypted data to the security of your backups.
   The security of backups is often very low, especially if backup plugins are involved.
2. Site owners/end users frequently modify the `wp-config.php` file, and there
   is no shortage of Tutorials online recommending to first back up the `wp-config.php` file
   to something like `wp-config.php.bak` before making changes.
   The backup of config files are then often not deleted and are accessible from the web server.
3. Plugins regularly modify, parse and read `wp-config.php` which increases the overall attack surface of the secrets.
4. Plugins often display `wp-config.php` in the WordPress admin area, which increases the overall attack surface.
5. It's not possible to make the secrets "read-only/immutable" since other plugins might need to write
   to `wp-config.php` (see 3.).

## The best ways to store secrets

### 1. Storing secrets in a KMS with instance restricted access

If you are running your WordPress site on AWS EC2, it
is possible to use the AWS Key Management Service (KMS) to store your secrets **and**
[restrict access to the KMS to your EC2's virtual private cloud (VPC)](https://aws.amazon.com/de/blogs/security/how-to-use-policies-to-restrict-where-ec2-instance-credentials-can-be-used-from/)

This is very secure, since only your EC2 server itself can access the secrets
and all access to the KMS is audited.

However, this method is most likely out of reach for most WordPress sites.

It's also important to note that this method requires a network
request to KMS for each WordPress request, so some form of local (object) caching might be required.

The general concept is that you fetch secrets from the KMS before or when your Fortress loader is included and define
them as PHP constants.

```php
// This is example code, you need to implement the actual fetching of the secret.
define('SNICCO_FORTRESS_TOTP_ENCRYPTION_SECRET_HEX', fetchFromKMS('fortress-totp-encryption-secret'));
// Repeat for all secrets
```

To compromise secrets, you most likely need to be able to alter the running code on the EC2 instance,
which constitutes a full compromise of the server in itself.

### 2. Storing secrets in an external KMS

This is similar to the above, but now you need a new
set of secrets to authenticate against your KMS.
It's ["turtles all the way down"](https://en.wikipedia.org/wiki/Turtles_all_the_way_down).

If somebody compromises the secret that you use to authenticate against the KMS,
they can fetch all your secrets from the KMS.

However, the big advantage is that you
can fully audit all access to the secrets and there are tools
available to
automatically [alarm on suspicious access patterns](https://www.secureworks.com/research/detecting-the-use-of-stolen-aws-lambda-credentials).

This method is likely also out of reach for most WordPress sites.

### 3.1 PHP constants stored in a dedicated file outside the web root

This is the method that
the [default installation guide of Fortress uses](../02_installation.md#create-fortress-secrets).

All secrets are defined as PHP constants in a file that is included
before Fortress is booted.

This method is the most flexible and works on any hosting setup, while still
providing acceptable security.

Secrets can be compromised if a site contains a vulnerability
that allows reading the data of random files without authorization.

Using a dedicated file outside the web root is
much [superior to storing secrets in the `wp-config.php` file](#dont-store-secrets-in-wp-configphp).

For additional protection against
file disclosure vulnerabilities, you could use a random file name for the secret file.
 
However, you need to find a way to include the random file before Fortress boots in a way
that does not disclose the path of the file itself.

The only thing that comes to mind is to use PHP .ini `auto_prepend_file` directive
and to set the .ini configuration via something like the PHP-FPM pool configuration.

This is admittedly, security by obscurity, but sometimes unconventional solutions are required in the WordPress
ecosystem.

### 3.1 ENV variables that contain a file path

ENV variables that contain a file path are likewise
a good way to store secrets.

As with [PHP constants in a file](#31-php-constants-stored-in-a-dedicated-file-outside-the-web-root),
the only way to compromise the secrets is to be able
to read random files from the filesystem.

Additionally, the file path can be set to a random file, although this is
admittedly security by obscurity.
Furthermore, keep in mind that almost everything in Linux is a file, so if
you can read from `/proc/self/environ` or `/proc/<pid>/environ`, you get
the file path of the secret file, even if it's random.

Nonetheless, this is a great way to provide secrets if you're using
Docker and [Docker secrets](https://docs.docker.com/engine/swarm/secrets/)

### 4. ENV variables that contain the secret value

The issue with plain ENV variables is that they
have multiple attack vectors for compromise.

- They can be comprised via file disclosure vulnerabilities by reading the `/proc/self/environ` or `/proc/<pid>/environ`
  files.
- They are disclosed by random calls to `phpinfo()`.
- Many plugins store the value of `$_SERVER`, `$ENV` and `getenv` in debug logs.
- Many plugins send the value of `$_SERVER`, `$ENV` and `getenv` to their own remote APIs.

Furthermore, it's a bite more challenging to provide the same ENV variables
to both CLI and web requests (unless you're using something like Docker).

For these reasons, storing secrets in ENV variables is not recommended,
and usually you can always swap this approach for either:

- a file path that points to a file.
- PHP constants in a file.

### Which method to choose?

1. If you are able to use a KMS, use a KMS.
2. If you can't use a KMS, use the default PHP constants in a file method, **unless**
   you are using Docker secrets, which might it be easier to manage ENV variables, but
   the security implications are roughly the same.

## Generating new secrets

Fortress contains a CLI script that can be called to generate new
sets of secrets and output them either as a PHP file or as JSON.

- Each call to the script generates new secrets.
- Secrets are only output to the console and are not stored anywhere.

```console
$ php /path-to-fortress/bin/prod/generate-secrets.php --php-file

<?php

declare(strict_types=1);

// Automatically generated Fortress secrets. NEVER EDIT MANUALLY.
define('SNICCO_FORTRESS_PASSWORD_ENCRYPTION_KEY_HEX', '4051eaa764093a1e0303cff4de3f9166d6ad4a5af0b1f9edfc3ada00430bc8fd');
define('SNICCO_FORTRESS_DEVICE_ID_AUTHENTICATION_KEY_HEX', '129d1db6abae31346bf02d7a08fde588840a5dafdd8941be93d71f07b611530c');
define('SNICCO_FORTRESS_TOTP_ENCRYPTION_SECRET_HEX', '04e4cfbfc724b4fe664cfbbd21f38d51166c3669c43a63352dd90461d6a096d6');
define('SNICCO_FORTRESS_LIBSODIUM_GENERIC_HASH_KEY_HEX', 'deaea8fad7e13c2167d69db32d99579ad70a70d4bb12e7e853fdcd3c0587b9a1');
define('SNICCO_FORTRESS_LIBSODIUM_GENERIC_ENCRYPTION_KEY_HEX', 'b90c134244815b9e88aa0d02aed72f649baa4f6d259df0728a031f111e62d329');
```

or, as JSON:

```console
$ php /path-to-fortress/bin/prod/generate-secrets.php --json

{
    "SNICCO_FORTRESS_PASSWORD_ENCRYPTION_KEY_HEX": "3a9c9b68e85ae0ece85157b61c7d514893ce22151cfb017654297b3681cb9c38",
    "SNICCO_FORTRESS_DEVICE_ID_AUTHENTICATION_KEY_HEX": "b63bcb75e54a37b13919ad1b98a87bd961f54875c6526730c26b33de79028870",
    "SNICCO_FORTRESS_TOTP_ENCRYPTION_SECRET_HEX": "11432d952488870855fb58e366aafa8182919fd166aaf9802d508381a89b7fb6",
    "SNICCO_FORTRESS_LIBSODIUM_GENERIC_HASH_KEY_HEX": "a7bacd10d6bf186f701d15ba52c03707ad29d16b57bc12b529842ef001cad329",
    "SNICCO_FORTRESS_LIBSODIUM_GENERIC_ENCRYPTION_KEY_HEX": "5b06acc89a6364cca5eabf490746aaa73aba41c8814d634179818d697a59fa8c"
}
```