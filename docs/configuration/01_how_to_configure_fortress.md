# How to configure Fortress

<!-- TOC -->
* [How to configure Fortress](#how-to-configure-fortress)
  * [Approach](#approach)
  * [How configuration is stored and loaded](#how-configuration-is-stored-and-loaded)
  * [Configuration sources](#configuration-sources)
    * [Baseline configuration](#baseline-configuration)
    * [Appliance configuration file](#appliance-configuration-file)
    * [Server configuration file](#server-configuration-file)
    * [Site configuration file](#site-configuration-file)
    * [PHP API (experimental)](#php-api-experimental)
    * [Viewing configuration sources](#viewing-configuration-sources)
    * [Testing configuration sources](#testing-your-configuration-sources)
  * [Viewing your current configuration](#viewing-your-currently-cached-configuration)
  * [Clearing the configuration cache](#clearing-the-configuration-cache)
    * [Self-invalidation](#self-invalidation)
      * [Custom parameters](#custom-parameters)
<!-- TOC -->

## Approach

End-users without an advanced understanding of information security should not be allowed to configure anything security related, including Fortress.

Right now, **Fortress has no configuration UI**. That's on purpose.

Instead, we invested countless hours creating a baseline configuration that works for 95% of use cases.

That said, for the remaining genuinely unique 5% of use cases, **Fortress is configurable down to the deepest level**
and can accommodate all needs.

## How configuration is stored and loaded

Fortress takes a unique approach to storing its configuration.
Instead of mindlessly dumping everything into the `wp_options` table, Fortress saves its "compiled" configuration
as a **`.php` file that returns a plain PHP array**. Reading the cached configuration then fully benefits from OPCache, and
the performance is way superior to reading from the database on every request.

During its boot process, Fortress checks if its configuration has already been "compiled" and cached.

If a cached configuration file is found, Fortress will use it as is. Otherwise, the configuration cache is rebuilt from
the following sources.

> **The cached configuration should never be changed manually!**

## Configuration sources

Fortress takes the following sources into account when building its cached configuration.

**Each configuration source has the option to overwrite previous ones.**

`baseline config < appliance config < server config < site config`

### Baseline configuration

The baseline configuration is defined in the [config/fortress.php](../../config/fortress.php) file.

Never change the baseline configuration. You will lose all changes after updating Fortress.

### Appliance configuration file

If you want to customize Fortress for all your clients as part of your [appliance license](../getting-started/05_appliance_distribution.md), you can define a PHP constant (before Fortress boots) that stores the path to a JSON configuration file.

The configuration file should be placed outside the webroot!

Fortress will read the file contents and **overwrite** the baseline configuration on a per-key basis.

```php
define('SNICCO_FORTRESS_APPLIANCE_CONFIG_FILE', '/etc/acme-host/fortress-defaults.json');
```

If `SNICCO_FORTRESS_APPLIANCE_CONFIG_FILE` is defined, it has to be an existing file that is readable
by the PHP process where Fortress runs.

If the `/etc/acme-host/fortress-defaults.json` file has the below contents:

```json
{
    "modules": [
        "auth"
    ],
    "auth": {
	"skip_2fa_setup_duration_seconds": 900,
        "max_totp_attempts_before_lockout": 10
    }
}
```

- Only the auth module would be enabled.
- TOTP setup can be skipped for 900 seconds (15 minutes)
- An account is locked after ten failed TOTP attempts.

Refer to the [complete configuration reference](02_configuration_reference.md) for all the available options.

### Server configuration file

End-users can customize Fortress for all sites on a server by defining a PHP constant (before Fortress boots)
that stores the path to a JSON configuration file.

The configuration file should be placed outside the webroot!

Fortress will read the file contents and **overwrite** the baseline configuration on a per-key basis.

```php
define('SNICCO_FORTRESS_SERVER_CONFIG_FILE', '/etc/fortress/server-config.json');
```

The `/etc/fortress/server-config.json` file has the below content:

```json
{
    "modules": [
        "auth",
        "session"
    ],
    "auth": {
        "max_totp_attempts_before_lockout": 20
    }
}
```

and the above [appliance config](#appliance-configuration-file) is also used.

- The auth AND session module would be enabled - Overwrites the appliance config.
- TOTP setup can be skipped for 900 seconds (15 minutes) - appliance config is maintained.
- An account is locked after 20 failed TOTP attempts - Overwrites the appliance config.

If you have purchased an [appliance license](../getting-started/05_appliance_distribution.md), it makes sense to already
define the `SNICCO_FORTRESS_SERVER_CONFIG_FILE` constant and set it to a fixed value across your entire platform.

Fortress will only read its content if the defined file exists.

### Site configuration file

End-users can also customize Fortress for an individual site by defining a PHP constant (before Fortress boots)
that stores the path to a JSON configuration file.

The configuration file should be placed outside the webroot!

Fortress will read the file contents and **overwrite** the baseline configuration on a per-key basis.

```php
define('SNICCO_FORTRESS_SITE_CONFIG_FILE', '/var/www/snicco.io/fortress-config.json');
```

If you have purchased an [appliance license](../getting-started/05_appliance_distribution.md), it makes sense to already
define the `SNICCO_FORTRESS_SITE_CONFIG_FILE` constant and set it to a fixed value across your entire platform.

Fortress will only read its content if the defined file exists.

### PHP API (experimental)

The last option to customize the configuration before its cached is by utilizing
the [`PublicConfigAPI`](../../src/Shared/Infrastructure/PublicConfigAPI.php) class.

You can obtain an instance of this class by hooking in the WordPress hook named:

- `Snicco\Enterprise\Fortress\Shared\Infrastructure\PublicConfigAPI::class`.

The primary use case for this is setting configuration options that are "tricky" to represent as JSON (relative directories, etc.)

```php
<?php
// some-file-that-loads-before-fortress.php
use Snicco\Enterprise\Fortress\Shared\Infrastructure\PublicConfigAPI;

add_action(PublicConfigAPI::class, function (PublicConfigAPI $config_api) :void {
    // $config_api->addCustomTemplateDirectory(__DIR__.'/templates');
});

// This syntax also works.
add_action(
    Snicco\Enterprise\Fortress\Shared\Infrastructure\PublicConfigAPI::class, 
    function (PublicConfigAPI $config_api) :void {
    
    // $config_api->addCustomTemplateDirectory(__DIR__.'/templates');
    
});

```

### Viewing configuration sources

Fortress contains a CLI command that can be used to view all your registered configuration
sources as JSON. 

It's very useful for troubleshooting how your configuration sources make up the final
cached configuration.

```shell
wp snicco/fortress shared config:sources
```

Example output:

```json
{
    "server": {
        "type": "server",
        "file_path": "/etc/fortress/server.json",
        "file_exists": false,
        "content": null
    },
    "site": {
        "type": "site",
        "file_path": "/var/www/site.json",
        "file_exists": false,
        "content": null
    }
}
```

Refer to the [full command reference](../wp-cli/readme.md#config-sources) for further information.

### Testing your configuration sources

Fortress contains a CLI command to test that the current configuration sources
can build a valid configuration cache.

The command performs deep semantic validation of the entire configuration, 
and you should always use it if you are editing any configuration source.

Think of it like as the `nginx -t` command that you run before `nginx reload`.

```shell
wp snicco/fortress shared config:test
```

If all configuration sources are valid, Fortress CLI will ask you interactively whether
you want to reload the cache so that the current configuration sources become active.

If you are running this command in an automated environment, you can also provide the `--reload-on-success`
flag to perform the reload automatically.

```shell
wp snicco/fortress shared config:test --reload-on-success
```

Fortress will provide you with detailed troubleshooting information if any sources are invalid.

An example output might be:

```json
{
  "site": {
    "errors": {
      "options": {
        "session.table_name": [
          "The table not should not start with the db prefix 'wp_'"
        ]
      }
    },
    "warnings": {
      "options": {
        "theme_css_file": [
          "The theme CSS file should be a relative URL path in order for staging/production environments to work without cache busting."
        ],
        "auth.max_totp_attempts_before_lockout": [
          "Users will be locked out after 1 failed 2FA attempt. This is probably not what you want."
        ],
        "auth.require_2fa_for_roles.admistrator": [
          "There is no role named 'admistrator'. It might be a typo or the role is registered dynamically by a plugin."
        ]
      }
    }
  }
}
```

Warnings don't necessarily mean that your configuration is invalid but should be reviewed.
The configuration cache can always be rebuilt if there are warnings without breaking the site.
Errors on the other hand will prevent the configuration cache from being rebuilt.

If warnings are present, Fortress will interactively ask you whether you want to continue with the rebuild.

If you are running this command in an automated environment, you can also provide the `--ignore-warnings`
flag.

```shell
wp snicco/fortress shared config:test --ignore-warnings --no-interaction
```

If `--ignore-warnings` is provided, Fortress returns a success (`0`) exit code instead of a failure (`1`).

`--ignore-warnings` can also be used in combination with `--reload-on-success`.

```shell
wp snicco/fortress shared config:test \
  --ignore-warnings \
  --reload-on-success \
  --no-interaction
```

Refer to the [full command reference](../wp-cli/readme.md#configtest) for further information.

## Viewing your currently cached configuration

The following CLI command can be used to display the currently cached configuration as JSON.

```shell
wp snicco/fortress shared cache:config
```

You can display specific parts of the configuration like so:

```shell
wp snicco/fortress shared cache:config --key=fortress.auth
```

```json
{
    "totp_secrets_table_name": "snicco_fortress_totp_secrets",
    "totp_sha_algo": "sha1",
    "skip_2fa_setup_duration_seconds": 1800,
    "require_2fa_for_roles_before_login": [],
    "max_totp_attempts_before_lockout": 5,
    "require_2fa_for_roles": [
        "administrator",
        "editor"
    ]
}
```

Refer to the [full command reference](../wp-cli/readme.md#cache-config) for further information.

## Clearing the configuration cache

Typically, you should not need to clear Fortress's configuration cache manually. 

If you change any of your configuration sources, it is always preferred to run the `shared config:test` command, as all your current configuration sources will be
thoroughly validated before applying them.

```shell
wp snicco/fortress shared config:test --reload-on-success
```

If you need to clear the cache manually, you can run the [`shared cache:clear`](../wp-cli/readme.md#cache--clear) command.

### Self-invalidation

Fortress will automatically clear its caches and reload all configuration sources if any of the following values change on the WordPress site:

- The Fortress version.
- Changes to the **relative path** to the WP-admin area (See [`admin_url`](https://developer.wordpress.org/reference/functions/admin_url/)).

In automated environments,
it's recommended to run the `config:test` command before updating Fortress in order to prevent a cache build
with invalid configuration, which would lead to Fortress being unable to boot.

#### Custom parameters

You can extend this list by providing your own cache invalidation parameters by defining
the following ENV variable, either by setting in the `$_SERVER` super global, or by passing it through the SAPI.

```php
$_SERVER['SNICCO_FORTRESS_EXTRA_CACHE_INVALIDATION_PARAMS'] = 'your-values-here';
```

`SNICCO_FORTRESS_EXTRA_CACHE_INVALIDATION_PARAMS` must be defined before Fortress's main plugin file is loaded to have an effect. 
Furthermore, if defined, the value **MUST** be a `non-empty-string`.

You can use this feature, for example, to invalidate the Fortress cache after
deploying a GIT-controlled site where the Fortress configuration files live in your repository.

```php
$_SERVER['SNICCO_FORTRESS_EXTRA_CACHE_INVALIDATION_PARAMS'] = 'your-git-commit-hash';
```

---

Next: [Complete configuration reference](02_configuration_reference.md)
