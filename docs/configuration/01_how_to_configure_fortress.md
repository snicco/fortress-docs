# How to configure Fortress

<!-- TOC -->
* [How to configure Fortress](#how-to-configure-fortress)
  * [Philosophy](#philosophy)
  * [How configuration is loaded](#how-configuration-is-loaded)
  * [Configuration sources](#configuration-sources)
    * [Example: How configuration sources are combined](#example-how-configuration-sources-are-combined)
      * [Merging configuration values](#merging-configuration-values)
      * [Except notation](#except-notation)
      * [Locking configuration values](#locking-configuration-values)
    * [Viewing all configuration sources](#viewing-all-configuration-sources)
    * [Viewing the currently cached configuration](#viewing-the-currently-cached-configuration)
  * [Testing and reloading the configuration](#testing-and-reloading-the-configuration)
    * [Configuration errors](#configuration-errors)
    * [Configuration warnings](#configuration-warnings)
    * [Testing configuration in CI/CD pipelines](#testing-configuration-in-cicd-pipelines)
  * [Updating configuration sources programmatically](#updating-configuration-sources-programmatically)
    * [Example usage](#example-usage)
    * [Other rules](#other-rules)
  * [Automatically optimize the configuration](#automatically-optimize-the-configuration)
  * [Configuration cache self-invalidation](#configuration-cache-self-invalidation)
    * [Custom invalidation parameters](#custom-invalidation-parameters)
    * [Clear the cache automatically for git-based deployments](#clear-the-cache-automatically-for-git-based-deployments)
<!-- TOC -->

## Philosophy

Site-owners/end-users (and often developers) should not be allowed to configure anything security related, including
Fortress.

**Fortress has no configuration UI**. That's on purpose.

The baseline configuration works for 95% of use cases.

For the remaining, genuinely unique, 5% of use cases, **Fortress is configurable down to the deepest level**
and can accommodate all needs.

You can find the full configuration reference [here](02_configuration_reference.md).

## How configuration is loaded

During its boot process, Fortress checks if a "compiled" and cached
configuration file exists in the
configured [cache directory](../getting-started/02_installation.md#create-a-fortress-home-directory).

If a cached configuration exists, Fortress uses it as-is.

Otherwise, a new configuration cache is built from multiple
["configuration sources."](#configuration-sources)

The process of building a configuration cache from its sources involves heavy validation logic
that would be infeasible to run on every request.

For that reason, Fortress stores the validated configuration as a single `.php` file that returns a plain PHP array,

This approach is much faster than reading from the database on every request,
since PHP's OPCache caches the compiled configured.

Configuration sources are just files, which makes it easy to version control
them, and deploy them as part of a GIT-controlled site.

🚨 Never change the **cached** configuration manually!

## Configuration sources

Apart from its [baseline configuration](02_configuration_reference.md#overview),
Fortress can use an unlimited number of configuration
sources into account when building the configuration cache.

A configuration source can be:

- A `.json` file that contains a JSON object.
  ```json
  {
    "auth": {
       "require_2fa_for_roles": ["administrator"]
    }
  }
  ```
- A `.php` file that returns an array.
  ```php
  <?php
  
  return [
    'auth' => [
      'require_2fa_for_roles' => ['administrator']
    ]
  ];
  ```

Both of the above are equivalent.
PHP file configuration sources can contain any code that you want,
as long as requiring the file returns an array.

Register configuration sources by defining the `SNICCO_FORTRESS_CONFIG_SOURCES` constant
before Fortress boots. 

A minimal example looks like this:

```php
define('SNICCO_FORTRESS_CONFIG_SOURCES', [
    'site' => [
        'path' => '/path-to-fortress-home/config.json',
    ]
]);
```

or, with a PHP configuration source:

```php
define('SNICCO_FORTRESS_CONFIG_SOURCES', [
    'site' => [
        'path' => '/path-to-fortress-home/config.php',
    ]
]);
```

Valid settings for each configuration source are:

| Setting                | Description                                                                                                                                                  | Required | Default | Type               | Allowed Values                                                                                                                                                    |
|------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|----------|---------|--------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `path`                 | The absolute path to the configuration file.                                                                                                                 | Yes      | -       | `non-empty-string` |                                                                                                                                                                   |
| `must_exist`           | Whether the configuration file has to exist. The default of `false` allows predefining configuration sources in hosting integrations for site-owners to use. | No       | `false` | `boolean`          | `true`,`false`                                                                                                                                                    | `false`                                                                                                                                                           |
| `shared_between_sites` | Whether multiple sites on a server use this configuration source                                                                                             | No       | `false` | `boolean`          | `true`,`false`                                                                                                                                                    |
| `environment`          | The environment in which the configuration source is included. By default, a source is used in all WordPress environments.                                   | No       | -       | `non-empty-string` | `local`, `development`, `staging`, `production` <bre>See: [WordPress Environment Types](https://make.wordpress.org/core/2020/08/27/wordpress-environment-types/)) |

The name of each configuration source must be unique, and a non-empty string.

- 🚨 For security reasons,
misconfigured configuration sources will cause Fortress to refuse to boot and throw an exception.
- 🚨 All configuration sources should be stored outside the webroot!
- 🚨 Changes to configuration sources only take effect after the configuration cache is rebuilt.

The [default Fortress loader](../_assets/fortress-loader.php) defines
the following configuration sources by default:

> Note: `<path-to-fortress-home>` is the configured [Fortress home directory](../getting-started/02_installation.md#create-a-fortress-home-directory).


```php
\define('SNICCO_FORTRESS_CONFIG_SOURCES', [
    'server' => [
        'path' => '/etc/fortress/server.json',
        'shared_between_sites' => true,
    ],
    'site' => [
        'path' => "/<path-to-fortress-home>/config.json",
    ],
    'site.staging' => [
        'path' => "/<path-to-fortress-home>/config.staging.json",
        'environment' => 'staging',
    ],
]);
```

- A `server` configuration at `/etc/fortress/server.json` that can be used to set a base config for all sites on the server.
- A `site` configuration at `/path-to-fortress-home/config.json` that is unique for the site and active in all environments.
- A `site.staging` configuration at `/path-to-fortress-home/config.staging.json` that is unique for the site and only active in the staging environment.
  The `site.staging` configuration can be used to override (parts of) the `site` configuration in the staging environment.

It's completely optional to use custom configuration sources.
You can use an unlimited number of configuration sources, or none at all.

However, if you want to customize Fortress per-site,
it's highly recommended to name this configuration source: `site`.
This is because Fortress's [config CLI commands](#updating-configuration-sources-programmatically) use `site` as the fallback source name if no source name is explicitly provided.

### Example: How configuration sources are combined

Configuration sources are merged in the order that they're defined in `SNICCO_FORTRESS_CONFIG_SOURCES`.

Using the defaults from above,
Fortress loads its [baseline configuration](02_configuration_reference.md#overview) first.
Then,the `server` configuration is loaded (if the file exists), followed by the `site` configuration,
and if WordPress runs in the staging environment, the `site.staging` configuration is loaded last.

Each loaded configuration source can define a subset of configuration options
that **replace** said options in the previous configuration source (or the baseline).

To illustrate this concept, let's consider the following file contents.

**server:**

```json
{
  "auth": {
    "skip_2fa_setup_duration_seconds": 900,
    "max_totp_attempts_before_lockout": 10
  }
}
```

This configuration source on its own would have the following effects:

- An account is locked after ten failed TOTP attempts. The baseline default of five is overwritten.
- TOTP setup can be skipped for 15 minutes. The baseline default of 30 minutes is overwritten.
- All other configuration options would be inherited from the [baseline](02_configuration_reference.md#overview)

**site:**

```json
{
  "privileged_user_roles": [
    "administrator"
  ],
  "auth": {
    "max_totp_attempts_before_lockout": 20
  }
}
```

This would have the following effects on the final configuration:

- Only `administrators` are `privileged_user_roles`. The `baseline` default of `["administrator", "editor"]` is
  overwritten.
- 2FA setup can still be skipped for 15 minutes. The value of the `server` config is maintained.
- An account is locked after twenty failed TOTP attempts. The `site` config overwrites the value of the `server`
  config.

`site.staging` (this time, as a PHP file for variety) with the below content:

```php
<?php

return [
   'privileged_user_roles' => ['editor'],
    'auth' => [
      'max_totp_attempts_before_lockout' => 30
    ]
]
```

**If** WordPress runs in the staging environment, the `site.staging` configuration would have the following effects on the final configuration:

- Only `editors` are `privileged_user_roles`. The `site` config is **overwritten**, arrays are not merged by default.
- 2FA setup can still be skipped for 15 minutes. The value of the `server` config is maintained.
- An account is locked after thirty failed TOTP attempts. The `site.staging` config overwrites the value of the `site` config.

#### Merging configuration values

In the previous example,
the `site.staging` configuration completely overwrites the `site` configuration for the `privileged_user_roles` key.

Sometimes this is not what you want, and you'd rather add to the existing set of values.

For that, the special `:merge` notation can be used on any configuration option represented as an array.

The following combination of configuration sources

- **foo**:
  ```json
  {
    "privileged_user_roles": ["administrator"]
  }
  ```

- **bar**:
  ```json
  {
    "privileged_user_roles:merge": ["editor", "author"]
  }
  ```
- **baz**:
  ```json
  {
    "privileged_user_roles:merge": ["contributor"]
  }
  ```

- will result in the following final configuration:
  ```json
  {
    "privileged_user_roles": ["administrator", "editor", "author", "contributor"]
  }
  ```

- 🚨`:merge` notation ignores duplicated values that are already present in previous sources.
- 🚨`:merge` notation can only be used on configuration options that are arrays.

#### Except notation

The opposite of `:merge` is `:except` and works exactly as you would expect.

- **Server**:
  ```php
   <?php
  
   return [
        'privileged_user_roles' => ['administrator', 'editor', 'author'],
   ];
  ```

- **Site**:
  ```json
  {
    "privileged_user_roles:except": ["author"]
  }
  ```
- will result in the following final configuration:
  ```json
  {
    "privileged_user_roles": ["administrator", "editor"]
  }
  ```

- 🚨`:except` notation ignores missing values that are not present in previous sources.
- 🚨`:except` notation can only be used on configuration options that are arrays.

#### Locking configuration values

If you're a hosting or service provider, you might want to lock certain configuration
values in your [appliance configuration](#appliance-configuration-file) so that they cannot be changed by end-users.

The `:locked` notation allows you to do exactly that.

Suffixing a configuration key with `:locked` will prevent the key
from being overwritten by any other configuration source.

For example, the following configuration can be used
to ensure that customers cannot change the rate limit backed to the object cache.

```json
{
  "rate_limit": {
    "storage:locked": "database"
  }
}
```

- 🚨`:locked` notation ignores missing values that are not present in previous sources.

### Viewing all configuration sources

The [`wp fort config ls` command](../cli/readme.md#config-ls) can be used to view all configuration sources
that are defined on your site, along with their file paths and contents.

### Viewing the currently cached configuration

The [`wp fort config cache ls` command](../cli/readme.md#config-cache-ls) can be used to view the currently cached configuration.

## Testing and reloading the configuration

The workflow for changing configuration sources is similar to `nginx -t && nginx reload`.<br>

Fortress combines both of these steps into a single CLI command: [`wp fort config test --reload-on-success`](../cli/readme.md#config-test)

Unlike Nginx, Fortress makes it impossible to reload the configuration
if any of the configuration sources **semantically** invalid.

Hundreds of validations run against the combined configuration sources,
and if any of them fail, the configuration cache is not rebuilt and detailed error messages are provided.

The [`config test` command](../cli/readme.md#config-test) includes many validations against the current state of the WordPress site.

For example, the [Vaults and Pillars module](../modules/vaults_and_pillars/wordpress_options.md)
allows you to encrypt third-party API keys in the `wp_options` table.

If currently have an encrypted API key in the option table, and then you remove a said option
from your configuration source, the command will fail.

```json
{
  "site": {
    "errors": {
      "options": {
        "vaults_and_pillars.option_vaults": [
          "The following option (subsets) still have encrypted vaults in the database but are not present in the configuration. WordPress will not receive the correct values anymore for options: some_api_key"
        ]
      }
    }
  }
}
```

For each used configuration source, `config test` will
output a set of errors, warnings, or nothing if the configuration source is valid.

### Configuration errors

Configuration errors are such that would lead to a broken site, or major functionality
being broken if the configuration cache was rebuilt with the current sources.

A configuration error **always prevents reloading** the configuration.

Examples of configuration errors are:

- An unknown configuration key.
- An unreadable configuration source.
- A configuration source that does not return an array.
- An integer is used for a configuration value that expects a boolean.
- Two conflicting configuration options are used.
- The value of a configuration option is fundamentally incompatible with the current state of the WordPress site, such as
  our example with the Vaults and Pillars module above.

### Configuration warnings

A warning might indicate that you accidentally misconfigured an option, but
there is no way of knowing for sure.

To give an example, Fortress allows completely disabling WordPress application passwords.

```json
{
  "password": {
    "disable_application_passwords": true
  }
}
```

You will get a warning if you try to reload the above configuration on a site where at least
one user has an application password configured that might still be in use.

```json
{
  "site": {
    "warnings": {
      "options": {
        "password.disable_application_passwords": [
          "1 user has an application password in the database. Setting this option to true means they will not be able authenticate anymore with their application password."
        ]
      }
    }
  }
}
```

It's not an error because there are legitimate reasons to disable application passwords
even if there are still some in the database.

Generally, Fortress keeps configuration warnings to a minimum, and it's either an error or nothing.

A warning does not prevent reloading the configuration, but you need to manually review the warning 
and decide whether to proceed with the reload.

If you run `config test` interactively, you will be asked whether you want to proceed with the reload.
In automated scripts, the `--ignore-warnings` flag can be used to ignore warnings.

🚨 Reloading the configuration with a warning is **always safe**, and will never break a site.

### Testing configuration in CI/CD pipelines

If you are running the `wp fort config test` command in CI/CD pipelines,
you can use the `--skip-stateful-checks` flag to skip any validations that
requirement interactions with the current state of the WordPress site (database, network IO, etc.)

However, this leaves you at risk of deploying a configuration that is
valid locally, but might not be valid on the production site.

There are two ways to remedy this:

1. If your deployment pipeline has the capability to rollback deployments, run the `config test` command without<br>
   `--skip-stateful-checks` in a post-deployment step.
2. The `config test` command supports reading configuration sources from STDIN for previewing the configuration
   without rebuilding the cache.
   This can be used to validate the configuration on the production site before
   deploying it.
   <br><br>
   For example, to test if application passwords can be safely disabled on the production site, you can run:
   ```shell
   echo '{"password": {"disable_application_passwords": true}}' | wp fort config test --stdin-source=site
   ```

## Updating configuration sources programmatically

You can, of course, manually edit configuration sources with a text editor
and then run the `wp fort config test --reload-on-success` command to validate and reload the configuration.

However, it's much more convenient to update configuration sources with the Fortress CLI.

The [`wp fort config update` command](../cli/readme.md#config-update) can be used to manipulate configuration sources programmatically.

It includes:

- Autocompletion of configuration keys.
- The full validation logic of the `config test` command, **it's impossible to make invalid updates**.
- The ability to automatically reload the configuration if validation is successful.
- The ability to preview changes with a `--dry-run` flag.
- Bulk update many configuration settings atomically in a single command.
- The ability to update nested configuration options with dot notation.
- and much more.

The syntax is as follows:

```shell
wp fort config update change1 change2 ...
```

A change can be one of the following:

- A deletion of an option, which is represented by the key prefixed with a `-`.
- An update of an option, which is represented by the key followed by a `=` and the new value.
- An addition to a list/array, which is represented by the key followed by a `+=` and the new value.
- A removal from a list/array, which is represented by the key followed by a `-=` and the value to remove.

### Example usage

Assuming the following site configuration is currently the following:

```json
{
  "privileged_user_roles": [
    "administrator"
  ],
  "auth": {
    "require_2fa_for_roles": [
      "administrator",
      "editor"
    ],
    "skip_2fa_setup_duration_seconds": 1800
  }
}
```

Running the following command:

```shell
wp fort config update --dry-run \
  code_freeze.enabled=auto \
  privileged_user_roles+=author \
  auth.require_2fa_for_roles-=editor \
  -auth.skip_2fa_setup_duration_seconds 
```

which reads as

- "Set the `code_freeze.enabled` option to `auto`."
- "Then, add `author` to the `privileged_user_roles` list."
- "Then, remove `editor` from the `auth.require_2fa_for_roles` list."
- "Then, remove the `auth.skip_2fa_setup_duration_seconds` option."

will result in the below final configuration:

```json
{
  "privileged_user_roles": [
    "administrator",
    "author"
  ],
  "auth": {
    "require_2fa_for_roles": [
      "administrator"
    ]
  },
  "code_freeze": {
    "enabled": "auto"
  }
}
```

The dots (".") in the changes are used to navigate nested configuration options.

For example: `code_freeze.enabled` expands to:

```json
{
  "code_freeze": {
    "enabled": "auto"
  }
}
```

Multiple levels of nesting are possible, as long as the resulting configuration options exist.

For example: `session.idle_timeout_per_cap.adminisistrator` expands to:

```json
{
  "session": {
    "idle_timeout_per_cap": {
      "administrator": 1800
    }
  }
}
```

### Other rules

- There is no limit on the number of changes that can be made in a single command.
  Spaces separate changes.
- Changes are applied exactly in the order they are provided.
- `:merge`, `:except`, and `:locked` notations can be used after keys.<br>For example:
    ```shell
    wp fort config update privileged_user_roles:merge+=author
    ```
  Results in:
    ```json
    {
      "privileged_user_roles:merge": [
        "author"
      ]
    }
    ```
- Since all CLI values are strings, the following conversion rules are applied:
    - `"true"` and `"false"` are converted to boolean `true` and `false`.
    - Numbers are converted to integer: `"100"` becomes `100`.
    - Implicit conversion can be disabled by suffixing a value with `:string`.
        - `"1:string"` would be treated as a string `"1"` instead of an integer `1`.
        - `"true:string"` would be treated as a string `"true"` instead of a boolean `true`.
- The values of `+=` and `-=` changes can be comma seperated, to add or remove many values at once from a list.
  ```shell
    wp fort config update privileged_user_roles+=author,contributor
   ```
- The update operation (`=`) can contain JSON arrays:
  ```shell
  wp fort config update \
    auth.require_2fa_for_roles='["administrator", "editor"]'
  ```
  or JSON objects:
  ```shell
  wp fort config update \
    auth='{"require_2fa_for_roles": ["administrator", "editor"], "skip_2fa_setup_duration_seconds": 1800}'
  ```
  Note: the above command is the same as the following:
  ```shell
  wp fort config update \
    auth.require_2fa_for_roles='["administrator", "editor"]' \
    auth.skip_2fa_setup_duration_seconds=1800
  ``` 
- By default, trying to delete any option that is not present in the configuration will cause the command to fail.
  This behavior can be changed by using the `--ignore-missing-delete` flag.
- You can update any defined source with the `--source=applicane|server|site` option.
  However, this should be done carefully since you might be affecting multiple sites with a single change.
  For that reason,
  the configuration is not reloaded automatically when using this option, and you will need to run `config test` on all
  sites to apply the changes.

## Automatically optimize the configuration

Fortress contains a [`wp fort config optimize` command](../cli/readme.md#config-optimize) that will
automatically optimize the **site** configuration sources depending on the
current state of the WordPress site.

This command will only make safe changes, and running it will never break a site's
configuration.

If you are a hosting or service provider, it's recommended
that you run this command periodically to ensure that the most secure configuration
is used for a site.

Currently, the following optimizations are performed:

- [Application passwords will be disabled](../modules/password/readme.md#disable-application-passwords) if no users have
  an application password in use.
- [Support for legacy hashes will be disabled](../modules/password/password-hashing.md#disallowing-legacy-hashes) after
  all user password hashes have been [upgraded](../modules/password/password-hashing.md).

Like the `config update` command, `config optimize` goes through
the full config validation cycle, and will not perform any changes if the configuration
is invalid.

Furthermore, any user-defined configuration sources will always be respected.

For example, if **any** used configuration source explicitly allows application
passwords:

```json
{
  "password": {
    "disable_application_passwords": false
  }
}
```

Then the `config optimize` command not modify the setting, even if it is safe and more secure to do so.

Yu can also use the `--dry-run` flag, to preview possible changes,
and then apply them manually.

## Configuration cache self-invalidation

Fortress will automatically reload the configuration cache if any of the following changes
on a WordPress site:

- The Fortress version.
- Changes to the **relative path** to the WP-admin area. This should never happen unless you move the `wp-admin`
  directory.
- Converting a site from multisite to single site or vice versa.

🚨 If [Fortress updates](../getting-started/03_updates.md) are performed automatically, it's highly recommended
to test that the current configuration sources are all valid before updating Fortress.

Since updates the Fortress source code changes the version, a cache rebuild is triggered,
and if any sources are currently invalid, Fortress will refuse to boot for safety reasons
and abort the request.

### Custom invalidation parameters

You can extend the list of cache invalidation parameters by
either defining the `SNICCO_FORTRESS_EXTRA_CACHE_INVALIDATION_PARAMS` constant
before Fortress boots, or by setting it through the $_SERVER super global as an env value.

```php
// The env variable would be set in Docker or similar, 
// this is just for demonstration purposes.
$_SERVER['SNICCO_FORTRESS_EXTRA_CACHE_INVALIDATION_PARAMS'] = 'your-values-here';
```

- You can pass any value to `SNICCO_FORTRESS_EXTRA_CACHE_INVALIDATION_PARAMS`, but it **must** be a `non-empty-string`.

### Clear the cache automatically for git-based deployments

You can use self-validation to invalidate the Fortress cache after
deploying a Git-controlled site where the Fortress configuration source files are part of
your repository.

```php
define('SNICCO_FORTRESS_EXTRA_CACHE_INVALIDATION_PARAMS', 'your-git-commit-hash');
```

Alternatively, you can also run the `wp fort config test --reload-on-success`
command, after deploying a new version of your site.

---

Next: [Complete configuration reference](02_configuration_reference.md)
