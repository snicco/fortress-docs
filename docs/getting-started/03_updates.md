# How to update Fortress

Fortress does **not** perform any sort of self-updates.

To update Fortress, you need to perform the following steps:

1. Download the desired version via any of the [supported methods](02_installation.md#downloading-a-fortress-release).
2. Test if the current [Fortress configuration is valid](../configuration/01_how_to_configure_fortress.md#configuration-cache-self-invalidation)
3. Enable the WordPress maintenance mode with WP-CLI.
4. Replace the Fortress source code at `/path-to-mu-plugins/snicco-fortress/releases/current` with the new version.
5. Run the Fortress setup CLI command in case the new version requires a database migration or similar one time actions.
6. Disable the WordPress maintenance mode with WP-CLI.
7. Optional: If you're using [`OPCache`](https://www.php.net/manual/en/intro.opcache.php), you should clear it for the site.
   <br>It's not possible to give a general recommendation on how to clear the cache, nor if you need to, as it greatly depends on your server
   configuration and architecture.
   - [How to clear OPCache on Apache](https://ma.ttias.be/how-to-clear-php-opcache/#apache-running-as-mod_php)
   - [How to clear OPCache on Nginx](https://ma.ttias.be/how-to-clear-php-opcache/#nginx-running-as-fpm)
   - [Cachetool - Manage OPCache in the CLI](https://gordalina.github.io/cachetool/)

## Updating Fortress in non-Composer-based projects

Download/Upload the new Fortress version a temporary directory on your server
using the [GitHub API](02_installation.md#download-using-the-github-api) 
or the [GitHub UI](02_installation.md#download-using-the-github-ui).

You could for example download the new version to `/tmp/snicco-fortress`.

`/tmp/snicco-fortress` should contain the Fortress source code, such as the `main.php` file.

Then run the following commands:

```shell
# Test current configuration sources
wp fort config test

# Put WordPress into maintenance mode.
wp maintenance-mode activate

# Replace the current version with the new version.
mv -f /tmp/snicco-fortress /path-to-mu-plugins/snicco-fortress/releases/current

wp fort setup

# Disable maintenance mode
wp maintenance-mode deactivate

# If using OPCache, clear it.
# See #how-to-update-fortress
```

## Updating Fortress in Composer/Git-based projects

Composer handles the logic of downloading and updating Fortress via
the specified version in your project's `composer.json` file.

After you deploy a new version of your project, run the `wp fort setup` 
command on the **production** server to ensure that potential database migrations
or similar one-time actions are executed for the new version.