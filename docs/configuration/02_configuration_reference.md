# Complete configuration reference

<!-- TOC -->

* [Overview](#overview)
* [Root-level](#root-level)
    * [modules](#modules)
    * [url_namespace](#url_namespace)
    * [cli_namespace](#cli_namespace)
    * [challenges_table_name](#challenges_table_name)
    * [privileged_user_roles](#privileged_user_roles)
    * [theme_css_file](#theme_css_file)
* [Session module](#session-module)
    * [sudo_mode_timeout](#sudo_mode_timeout)
    * [sudo_mode_timeout_per_cap](#sudo_mode_timeout_per_cap)
    * [idle_timeout](#idle_timeout)
    * [idle_timeout_per_cap](#idle_timeout_per_cap)
    * [rotation_timeout](#rotation_timeout)
    * [rotation_timeout_per_cap](#rotation_timeout_per_cap)
    * [absolute_timeout](#absolute_timeout)
    * [absolute_timeout_per_cap](#absolute_timeout_per_cap)
    * [absolute_timeout_remembered_user](#absolute_timeout_remembered_user)
    * [absolute_timeout_remembered_user_per_cap](#absolute_timeout_remembered_user_per_cap)
    * [table_name](#table_name)
    * [remember_cookie_name](#remember_cookie_name)
    * [protected_pages](#protected_pages)
    * [protected_capabilities](#protected_capabilities)
    * [non_sudo_mode_recheck_frequency](#non_sudo_mode_recheck_frequency)
    * [disable_rotation_for_ajax_like_requests_per_cap](#disable_rotation_for_ajax_like_requests_per_cap)
* [Auth module](#auth-module)
    * [totp_secrets_table_name](#totp_secrets_table_name)
    * [totp_sha_algo](#totp_sha_algo)
    * [totp_recovery_codes_locked_to_user_id](#totp_recovery_codes_locked_to_user_id)
    * [skip_2fa_setup_duration_seconds](#skip_2fa_setup_duration_seconds)
    * [require_2fa_for_roles](#require_2fa_for_roles)
    * [require_2fa_for_roles_before_login](#require_2fa_for_roles_before_login)
    * [max_totp_attempts_before_lockout](#max_totp_attempts_before_lockout)
    * [magic_link_show_on_wp_login_form](#magic_link_show_on_wp_login_form)
    * [magic_link_allow_requesting_via_http](#magic_link_allow_requesting_via_http)
* [Password module](#password-module)
    * [password_policy_excluded_roles](#password_policy_excluded_roles)
    * [disable_application_passwords](#disable_application_passwords)
    * [allow_legacy_hashes](#allow_legacy_hashes)
    * [default_hash_strength](#default_hash_strength)
    * [auto_upgrade_hashes](#auto_upgrade_hashes)
    * [include_pluggable_functions](#include_pluggable_functions)
    * [disable_web_password_reset_for_roles](#disable_web_password_reset_for_roles)
* [Rate-Limit module](#rate-limit-module)
    * [storage](#storage)
    * [cache_group](#cache_group)
    * [device_id_cookie_prefix](#device_id_cookie_prefix)
    * [device_id_burst](#device_id_burst)
    * [device_id_refill_one_token_seconds](#device_id_refill_one_token_seconds)
    * [username_burst](#username_burst)
    * [username_refill_one_token_seconds](#username_refill_one_token_seconds)
    * [ip_burst](#ip_burst)
    * [ip_refill_one_token_seconds](#ip_refill_one_token_seconds)
    * [global_burst](#global_burst)
    * [global_refill_one_token_seconds](#global_refill_one_token_seconds)
    * [log_to_syslog](#log_to_syslog)
    * [syslog_daemon](#syslog_daemon)
    * [syslog_flags](#syslog_flags)
    * [syslog_facility](#syslog_facility)
* [Vaults & Pillars module](#vaults--pillars-module)
    * [strict_option_vaults_and_pillars](#strictoptionvaultsandpillars)
    * [option_pillars](#optionpillars)
    * [option_vaults](#optionvaults)
* [Code Freeze module](#code-freeze-module)
    * [enabled](#enabled)
<!-- TOC -->

## Overview

You can find a JSON schema of Fortress's configuration [here](schema.json). You can use the JSON schema in your favorite
IDE to provide syntax highlighting, auto-completion and validation.
This is highly recommended when editing Fortress configuration locally:

- [PHPStorm: How to add custom schema sources.](https://www.jetbrains.com/help/phpstorm/json.html#ws_json_schema_add_custom)
- [Visual Studio Code: How to add custom schema sources](https://code.visualstudio.com/docs/languages/json).

Below is the JSON representation of the baseline configuration of Fortress without any modification through
any of the supported [configuration sources](01_how_to_configure_fortress.md#configuration-sources).

> In reality, Fortress does not store its cached configuration as JSON but as a PHP array, as
> explained [here](01_how_to_configure_fortress.md#how-configuration-is-stored-and-loaded).
> Many of the values are created dynamically in the [fortress.php](../../config/fortress.php) config file during the
> first
> cache built.

Note: The below configuration is for a single site installation of WordPress.
The configuration for a multisite is slightly different.

```json
{
  "modules": [
    "password",
    "session",
    "auth",
    "rate_limit",
    "vaults_and_pillars",
    "code_freeze"
  ],
  "url_namespace": "/snicco-fortress",
  "cli_namespace": "snicco/fortress",
  "challenges_table_name": "snicco_fortress_challenges",
  "privileged_user_roles": [
    "administrator",
    "editor"
  ],
  "theme_css_file": "/wp-content/mu-plugins/snicco-fortress/default-theme.css",
  "session": {
    "sudo_mode_timeout": 600,
    "sudo_mode_timeout_per_cap": [],
    "idle_timeout": 1800,
    "idle_timeout_per_cap": [],
    "rotation_timeout": 1200,
    "rotation_timeout_per_cap": [],
    "absolute_timeout": 43200,
    "absolute_timeout_per_cap": [],
    "absolute_timeout_remembered_user": 86400,
    "absolute_timeout_remembered_user_per_cap": [],
    "table_name": "snicco_fortress_sessions",
    "remember_cookie_name": "snicco_fortress_remember_me",
    "protected_pages": [
      "/wp-admin/update-core.php",
      "/wp-admin/themes.php",
      "/wp-admin/theme-install.php",
      "/wp-admin/plugins.php",
      "/wp-admin/plugin-install.php",
      "/wp-admin/users.php",
      "/wp-admin/user-new.php",
      "/wp-admin/profile.php",
      "/wp-admin/update.php",
      "/wp-admin/options-*",
      "/wp-admin/options.php",
      "/wp-admin/authorize-application.php",
      "/wp-admin/tools.php",
      "/wp-admin/import.php",
      "/wp-admin/export.php",
      "/wp-admin/site-health.php",
      "/wp-admin/export-personal-data.php",
      "/wp-admin/erase-personal-data.php",
      "/wp-admin/theme-editor.php",
      "/wp-admin/plugin-editor.php",
      "/snicco-fortress/auth/totp/manage*"
    ],
    "protected_capabilities": [
      "administrator",
      "activate_plugins",
      "delete_plugins",
      "delete_themes",
      "delete_users",
      "edit_dashboard",
      "edit_files",
      "edit_plugins",
      "edit_theme_options",
      "edit_themes",
      "edit_users",
      "export",
      "import",
      "install_plugins",
      "install_themes",
      "manage_options",
      "promote_users",
      "remove_users",
      "list_users",
      "create_users",
      "switch_themes",
      "unfiltered_upload",
      "update_core",
      "update_plugins",
      "update_themes",
      "manage_categories",
      "delete_pages",
      "delete_private_pages",
      "delete_published_pages",
      "delete_others_pages",
      "delete_posts",
      "delete_private_posts",
      "delete_published_posts",
      "delete_others_posts",
      "edit_comment",
      "view_site_health_checks",
      "install_languages"
    ],
    "non_sudo_mode_recheck_frequency": 10,
    "disable_rotation_for_ajax_like_requests_per_cap": []
  },
  "auth": {
    "totp_secrets_table_name": "snicco_fortress_totp_secrets",
    "totp_sha_algo": "sha1",
    "totp_recovery_codes_locked_to_user_id": false,
    "skip_2fa_setup_duration_seconds": 1800,
    "require_2fa_for_roles_before_login": [],
    "max_totp_attempts_before_lockout": 5,
    "magic_link_show_on_wp_login_form": true,
    "magic_link_allow_requesting_via_http": true,
    "require_2fa_for_roles": [
      "administrator",
      "editor"
    ]
  },
  "password": {
    "password_policy_excluded_roles": [],
    "disable_application_passwords": true,
    "allow_legacy_hashes": true,
    "default_hash_strength": "moderate",
    "store_hashes_encrypted": false,
    "auto_upgrade_hashes": true,
    "include_pluggable_functions": true,
    "disable_web_password_reset_for_roles": [
      "administrator",
      "editor"
    ]
  },
  "rate_limit": {
    "storage": "auto",
    "cache_group": "snicco_fortress_rate_limits",
    "device_id_cookie_prefix": "device_id",
    "device_id_burst": 5,
    "device_id_refill_one_token_seconds": 20,
    "username_burst": 5,
    "username_refill_one_token_seconds": 900,
    "ip_burst": 20,
    "ip_refill_one_token_seconds": 1800,
    "global_burst": 100,
    "global_refill_one_token_seconds": 30,
    "use_hashed_ips": false,
    "log_to_syslog": true,
    "syslog_daemon": "snicco_fortress",
    "syslog_flags": 1,
    "syslog_facility": 32
  },
  "vaults_and_pillars": {
    "option_pillars": [],
    "option_vaults": [],
    "strict_option_vaults_and_pillars": false
  },
  "code_freeze": {
    "enabled": "auto"
  }
}
```

## Root-level

### modules

- Key: `modules`
- Type: `string[]`
- Default: `["password","session","auth","rate_limit", "vaults_and_pillars", "code_freeze"`
- Allowed values: `["password","session","auth","rate_limit", "vaults_and_pillars", "code_freeze"`

The `modules` option determines the actively used modules of Fortress.

Removing one or more modules will prevent the module from loading and disable all its functionality.

### url_namespace

- Key: `url_namespace`
- Type: `non-empy-string`
- Default: `/snicco-fortress`

The `url_namespace` is the shared prefix of all routes that Fortress manages.

Example:

By default, the 2FA management page is accessible at `/snicco-fortress/auth/totp/manage`.

Changing the value of `url_namespace` to `/1234/acme-host` would in result make the 2FA management page accessible
at `/1234/acme-host/auth/totp/manage`.

### cli_namespace

- Key: `cli_namespace`
- Type: `non-empy-string`
- Default: `/snicco-fortress`

The `cli_namespace` is the shared prefix of the Fortress CLI.

Example:

By default, the [Info](../wp-cli/readme.md#info) CLI command would look like this:

```shell
wp snicco/fortress shared info
```

Changing the value of `cli_namespace` to `acme-security` would mean that the Fortress CLI has to be used like so:

```shell
wp acme-security shared info
```

### challenges_table_name

- Key: `challenges_table_name`
- Type: `non-empty-string`
- Default: `snicco_fortress_challenges`

The table name (without prefix) where Fortress stores auth challenges.

### privileged_user_roles

- Key: `privileged_user_roles`
- Type: `string[]`
- Default: `["administrator","editor"]`

An array of WordPress user roles that Fortress considers to be privileged.

Example configuration for WooCommerce sites:

```json
{
  "privileged_user_roles": [
    "administrator",
    "editor",
    "shop_manager"
  ]
}
```

### theme_css_file

- Key: `theme_css_file`
- Type: `non-empty-string`
- Default: `/wp-content/mu-plugins/snicco-fortress/default-theme.css`

A path to an existing CSS file that defines CSS variables
to [customize the frontend appearance](../getting-started/03_white_label.md#appearance) of Fortress.

## Session module

- JSON namespace: `"session"`

### sudo_mode_timeout

- Key: `sudo_mode_timeout`
- Type: `positive-integer`
- Default: `600` (10 minutes)

The `sudo_mode_timeout` option is the interval in seconds during which Fortress will consider a session to be
in [sudo mode](../modules/session/sudo-mode.md) after
logging in.

### sudo_mode_timeout_per_cap

- Key: `sudo_mode_timeout_per_cap`
- Type: `array<string,positive-interger`
- Default: `[]`

The `sudo_mode_timeout_per_cap` option can be used if more fine-grained control of the sudo mode timeout is needed.

The following configuration sets the sudo mode timeout to 10 (`60*6=600`) minutes for users with the `mange_options`
capability, and to six hours (`60*60*6 = 18,000`) for everybody else.

```json
{
  "session": {
    "sudo_mode_timeout": 18000,
    "sudo_mode_timeout_per_cap": {
      "manage_options": 600
    }
  }
}
```

### idle_timeout

- Key: `idle_timeout`
- Type: `positive-interger`
- Default: `1800` (30 minutes)

The `idle_timeout` option is the interval in seconds after which a user without activity is logged out.

### idle_timeout_per_cap

- Key: `idle_timeout_per_cap`
- Type: `array<string,positive-interger`
- Default: `[]`

The `idle_timeout_per_cap` option can be used if more fine-grained control of
the [idle timeout](../modules/session/session-managment-and-security.md#the-idle-timeout) is needed.

The following configuration sets the idle timeout to 10 (`60*6=600`) minutes for users with the `mange_options`
capability, and to six hours (`60*60*6 = 18,000`) for everybody else.

```json
{
  "session": {
    "idle_timeout": 18000,
    "idle_timeout_per_cap": {
      "manage_options": 600
    }
  }
}
```

### rotation_timeout

- Key: `rotation_timeout`
- Type: `positive-interger`
- Default: `1200` (20 minutes)

The `rotation_timeout` is the interval after which the user's session token
is [rotated](../modules/session/session-managment-and-security.md#the-rotation-timeout).

### rotation_timeout_per_cap

- Key: `rotation_timeout_per_cap`
- Type: `array<string,positive-interger`
- Default: `[]`

The `rotation_timeout_per_cap` option can be used if more fine-grained control of the rotation timeout is needed.

The following configuration sets the rotation timeout to 10 (`60*6=600`) minutes for users with the `mange_options`
capability, and to six hours (`60*60*6 = 18,000`) for everybody else.

```json
{
  "session": {
    "rotation_timeout": 18000,
    "rotation_timeout_per_cap": {
      "manage_options": 600
    }
  }
}
```

### absolute_timeout

- Key: `absolute_timeout`
- Type: `positive-interger`
- Default: `43200` (12 hours)

The `absolute_timeout` is the maximum lifespan of a user session.

**This value only applies to users that DID NOT check the "remember_me" option during login!**

### absolute_timeout_per_cap

- Key: `absolute_timeout_per_cap`
- Type: `array<string,positive-interger`
- Default: `[]`

The `absolute_timeout_per_cap` option can be used if more fine-grained control of the absolute timeout is needed.

The following configuration sets the absolute timeout to six (`60*60*6 = 18,000`) hours for users with
the `mange_options` capability, and to 48 hours (`60*60*48 = 172,800`) for everybody else.

```json
{
  "session": {
    "absolute_timeout": 172800,
    "absolute_timeout_per_cap": {
      "manage_options": 18000
    }
  }
}
```

**This value only applies to users that DID NOT check the "remember_me" option during login!**

### absolute_timeout_remembered_user

- Key: `absolute_timeout_remembered_user`
- Type: `positive-interger`
- Default: `86400` (24 hours)

See: [`absolute_timeout`](#absolutetimeout), with the difference being that this option applies when a user checks the "
remember_me" option during login.

### absolute_timeout_remembered_user_per_cap

- Key: `absolute_timeout_remembered_user_per_cap`
- Type: `array<string,positive-interger`
- Default: `[]`

See: [`absolute_timeout_per_cap`](#absolutetimeoutpercap), with the difference being that this option applies when a
user checks the "
remember_me" option during login.

### table_name

- Key: `table_name`
- Type: `non-empty-string`
- Default: `"snicco_fortress_sessions"`

The name without the prefix of the database table that stores user sessions.

### remember_cookie_name

- Key: `remember_cookie_name`
- Type: `non-empty-string`
- Default: `"snicco_fortress_remember_me"`

Determines the name of a marker cookie that Fortress uses to determine if a user wants to be remembered.

### protected_pages

- Key: `protected_pages`
- Type: `string[]`
- Default:
    ```json 
    {
      "session": {
        "protected_pages": [
          "/wp-admin/update-core.php",
          "/wp-admin/themes.php",
          "/wp-admin/theme-install.php",
          "/wp-admin/plugins.php",
          "/wp-admin/plugin-install.php",
          "/wp-admin/users.php",
          "/wp-admin/user-new.php",
          "/wp-admin/profile.php",
          "/wp-admin/update.php",
          "/wp-admin/options-*",
          "wp-admin/authorize-application.php",
          "/wp-admin/tools.php",
          "/wp-admin/import.php",
          "/wp-admin/export.php",
          "/wp-admin/site-health.php",
          "/wp-admin/export-personal-data.php",
          "/wp-admin/erase-personal-data.php",
          "/wp-admin/theme-editor.php",
          "/wp-admin/plugin-editor.php",
          "/snicco-fortress/auth/totp/manage*"
        ]
      }
  }
    ```

The `protected_pages` option specifies URL paths that a user can only access if his session is still in sudo mode.

You can use a `*` character as a wildcard.

The following configuration would prevent users whose sessions are not in sudo mode anymore from accessing the entire
wp-admin area.

```json
{
  "session": {
    "protected_pages": [
      "/wp-admin/*"
    ]
  }
}
```

### protected_capabilities

- Key: `protected_capabilities`
- Type: `string[]`
- Default:
    ```json 
    {
      "session": {
        "protected_capabilities": [
          "administrator",
          "activate_plugins",
          "delete_plugins",
          "delete_themes",
          "delete_users",
          "edit_dashboard",
          "edit_files",
          "edit_plugins",
          "edit_theme_options",
          "edit_themes",
          "edit_users",
          "export",
          "import",
          "install_plugins",
          "install_themes",
          "manage_options",
          "promote_users",
          "remove_users",
          "list_users",
          "create_users",
          "switch_themes",
          "unfiltered_html",
          "unfiltered_upload",
          "update_core",
          "update_plugins",
          "update_themes",
          "manage_categories",
          "delete_pages",
          "delete_private_pages",
          "delete_published_pages",
          "delete_others_pages",
          "delete_posts",
          "delete_private_posts",
          "delete_published_posts",
          "delete_others_posts",
          "edit_comment",
          "view_site_health_checks",
          "install_languages"
       ]
      }
  }
    ```

The `protected_capabilities` option specifies capabilities that a user can only "has" if their session is still
in [sudo mode](../modules/session/sudo-mode.md).

The following configuration would only protect the `manage_options` capability.

```json
{
  "session": {
    "protected_capabilities": [
      "manage_options"
    ]
  }
}
```

The following configuration would make all capabilities accessible all the time, even if a session is not
in [sudo mode](../modules/session/sudo-mode.md).

```json
{
  "session": {
    "protected_capabilities": []
  }
}
```

### non_sudo_mode_recheck_frequency

- Key: `non_sudo_mode_recheck_frequency`
- Type: `positive-integer`
- Default: `10`

The `non_sudo_mode_recheck_frequency` option determines how often Fortress checks if a user's session has re-entered
the [sudo mode](../modules/session/sudo-mode.md).

This timeout is only relevant if the user is either about to cross the threshold of non being in sudo mode anymore,
or has crossed the sudo timeout already.

A value of `10` means that Fortress would check every ten **seconds** if the user has confirmed his credentials
AFTER his session had already crossed the sudo timeout.

No checks are made if the user is still in sudo mode.

### disable_rotation_for_ajax_like_requests_per_cap

- Key: `disable_rotation_for_ajax_like_requests_per_cap`
- Type: `string[]`
- Default: `[]`

The `disable_rotation_for_ajax_like_requests_per_cap` option can be used
to disable the rotation of session tokens for ajax like requests for users with any
of the specified capabilities.

The following configuration would prevent rotating session for subscribers and authors for
ajax like requests.

```json
{
  "session": {
    "disable_rotation_for_ajax_like_requests_per_cap": [
      "subscriber",
      "author"
    ]
  }
}
```

## Auth module

- JSON namespace: `"auth"`

### totp_secrets_table_name

- Key: `totp_secrets_table_name`
- Type: `non-empty-string`
- Default: `"snicco_fortress_totp_secrets"`

The name without the prefix of the database table that stores users' TOTP secrets.

### totp_sha_algo

- Key: `totp_sha_algo`
- Type: `non-empty-string`
- Default: `"sha1"`
- Allowed values: `"sha1" | "sha256" | "sha512"`

The `totp_sha_algo` option sets the hash algorithm that Fortress uses to generate six-digit one-time passwords.

Only change this if you are 100% sure what you are doing.

Most password manager apps (1Password, Google Authenticator, etc.) only support `sha1`.

### totp_recovery_codes_locked_to_user_id

- Key: `totp_recovery_codes_locked_to_user_id`
- Type: `boolean`
- Default: `false`

The `totp_recovery_codes_locked_to_user_id` option controls 
whether a user's 2FA recovery keys are hashed with their user ID as additional data.

This prevents an attacker with write-access to the database from swapping his hashed recovery
codes with the hashed recovery codes of another user (that presumably has an attacker-compromised password).

**This option should always be enabled for new sites**, but is currently turned
off by default because it will invalidate existing recovery codes that were
created prior to the introduction of this option.

### skip_2fa_setup_duration_seconds

- Key: `skip_2fa_setup_duration_seconds`
- Type: `positve-integer`
- Default: `1800` (30 minutes)

The interval in seconds a user can skip his mandatory 2FA setup **once**.

### require_2fa_for_roles

- Key: `require_2fa_for_roles`
- Type: `string[]`
- Default: [`privileged_user_roles`](#privilegeduserroles)

All users that have one of the defined roles will need to set up 2FA immediately
after logging in.

### require_2fa_for_roles_before_login

- Key: `require_2fa_for_roles_before_login`
- Type: `string[]`
- Default: `[]`

The `require_2fa_for_roles_before_login` option can be used to prevent users without confirmed 2FA credentials from
logging in.

This option is handy in the following scenario:

- A site only has a couple (or one) administrator.
- No administrators are added in the foreseeable future.

The following configuration:

```json
{
  "auth": {
    "require_2fa_for_roles_before_login": [
      "administrator"
    ]
  }
}
```

has the following effects:

- An administrator can only log in if they have confirmed 2FA credentials.
- No administrator will be able to deactivate 2FA for his account.

This significantly hardens the site against attacks that attempts to create an undetected admin account since the
account will not be able to log in due to missing 2FA credentials.

### max_totp_attempts_before_lockout

- Key: `max_totp_attempts_before_lockout`
- Type: `positive-integer`
- Default: `5`

This option represents the maximum number of failed 2FA attempts that Fortress will allow
before locking an account.

### magic_link_show_on_wp_login_form

- Key: `magic_link_show_on_wp_login_form`
- Type: `bool`
- Default: `true`

Controls whether Fortress shows a link to the custom Magic Link Login page
on the default wp-login.php page.

### magic_link_allow_requesting_via_http

- Key: `magic_link_allow_requesting_via_http`
- Type: `bool`
- Default: `true`

Controls whether Fortress allows anybody to request a magic login link
via the UI/HTTP.

Setting this option to `false` makes Fortress behave as is the functionality to request
Magic Links doesn't exist.

## Password module

- JSON namespace: `"password"`

### password_policy_excluded_roles

- Key: `password_policy_excluded_roles`
- Type: `string[]`
- Default: `[]`

An array of user roles that are excluded from complying with the password policy.

### disable_application_passwords

- Key: `disable_application_passwords`
- Type: `bool`
- Default: `true`

The `disable_application_passwords` option controls
whether [WordPress application passwords](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/)
are entirely disabled.

### allow_legacy_hashes

- Key: `allow_legacy_hashes`
- Type: `bool`
- Default: `true`

The `allow_legacy_hashes` option controls whether Fortress will try to validate password hashes created using the
insecure default hashing algorithm of WordPress.

Settings this option to `false` is highly recommended if one of the following conditions are met:

1. The WordPress site is brand new, with Fortress pre-installed.
2. All legacy password hashes have been upgraded by Fortress using
   the [WP-CLI command](../wp-cli/readme.md#upgrade-legacy-hashes).

### store_hashes_encrypted

- Key: `store_hashes_encrypted`
- Type: `bool`
- Default: `false`

The `store_hashes_encrypted` option controls whether Fortress will store argon2id password hashes encrypted.

You can change this setting at any time to true/false, without invalidating existing password hashes. 

More information: [Fortress password hashing](../modules/password/password-hashing.md#fortress-password-hashing).

### default_hash_strength

- Key: `default_hash_strength`
- Type: `non-empty-string`
- Default: `moderate`
- Allowed Values: `"interactive" | "moderate" | "sensitive"`

The `default_hash_strength` option determines how long it takes the libsodium PHP extension to compute the hash of a
password.

> For online use (e.g., logging in on a website), a one-second computation is likely the acceptable maximum.

Setting the value to `"moderate "` accomplishes roughly a one-second duration on most hardware.

You might need to set this value to  `"interactive"` on really slow hardware.
On the flip side, you might need to set it to `"sensitive"` on really fast hardware.

See: https://doc.libsodium.org/password_hashing/default_phf#guidelines-for-choosing-the-parameters <br>
See: https://www.php.net/manual/de/function.sodium-crypto-pwhash-str.php

### auto_upgrade_hashes

- Key: `auto_upgrade_hashes`
- Type: `bool`
- Default: `true`

The `auto_upgrade_hashes` option controls whether Fortress will automatically rehash a user's password after he entered
authenticated successfully.

The only scenario in which you would want to disable this option is if you don't want Fortress to modify any data since
you plan on removing it.

### include_pluggable_functions

- Key: `include_pluggable_functions`
- Type: `bool`
- Default: `true`

Whether Fortress should load its custom implementations of the "wp_***" pluggable password functions.

Fortress will not load it's custom password hashing at all if this option is set to `false`
and WordPress's default md5-based password hashing will be used. 

Setting this option to `false` will also disable most of Fortress's password CLI commands.

Setting this option to `false` is only recommended if you plan on removing Fortress without
the need to reset passwords for all users.

Even if this is set to `false`, you can still use all other functionality of the password module, like enforcing password policies, disabling
password resets, etc.

### disable_web_password_reset_for_roles

- Key: `disable_web_password_reset_for_roles`
- Type: `string[]`
- Default: [`privileged_user_roles`](#privilegeduserroles)

An array of WordPress user roles for which password resets in the admin and frontend UI are disabled.
UI-based password resets are always a backdoor as they depend on the security of your email account.

The following configuration:

```json
{
  "password": {
    "disable_web_password_reset_for_roles": [
      "administrator"
    ]
  }
}
```

has the following effects:

- Admins can not reset their password on their profile page.
- Admins can not reset the password of another admin.
- Admins can not request a password reset link.

**Password resets [using the WP-CLI](../wp-cli/readme.md#password-reset) are always possible!**

## Rate-Limit module

- JSON namespace: `"rate_limit"`

### storage

- Key: `storage`
- Type: `non-empty-string`
- Default: `auto`
- Allowed values: `"auto" | "database"`

The `storage` option can be used to explicitly tell Fortress to use the database as
a rate-limit storage.

### cache_group

- Key: `cache_group`
- Type: `non-empty-string`
- Default: `snicco_fortress_rate_limits`

The `cache_group` option will either be used as a WP Object cache group for storing rate limits or, if no object cache
is available, as the database table name for storing rate limits.

### device_id_cookie_prefix

- Key: `device_id_cookie_prefix`
- Type: `non-empty-string`
- Default: `device_id`

All device ID cookies will start with the value of the `device_id` option.

### device_id_burst

- Key: `device_id_burst`
- Type: `positive-integer`
- Default: `5`

The number of failed login attempts a user can make with a valid device ID before the device ID throttling activates.

### device_id_refill_one_token_seconds

- Key: `device_id_refill_one_token_seconds`
- Type: `positive-integer`
- Default: `20`

The interval in seconds a user rate-limited by his device ID has to wait before he can make **ONE** more login request.

### username_burst

- Key: `username_burst`
- Type: `positive-integer`
- Default: `5`

The number of failed login attempts a user can make without device ID before the username throttling activates.

### username_refill_one_token_seconds

- Key: `username_refill_one_token_seconds`
- Type: `positive-integer`
- Default: `900` (15 minutes)

The interval in seconds that a user rate-limited by his username has to wait before he can make **ONE** more login
request for that same username.

### ip_burst

- Key: `ip_burst`
- Type: `positive-integer`
- Default: `20`

The number of failed login attempts that one IP address can make before the IP throttling activates.

### ip_refill_one_token_seconds

- Key: `ip_refill_one_token_seconds`
- Type: `positive-integer`
- Default: `1800` (30 minutes)

The interval in seconds that a rate-limited IP address has to wait before it can make **ONE** more login request.

### global_burst

- Key: `global_burst`
- Type: `positive-integer`
- Default: `100`

The number of failed logins that are considered "normal" across the entire site. Exceeding this limit will activate the
global login throttling.

### global_refill_one_token_seconds

- Key: `global_refill_one_token_seconds`
- Type: `positive-integer`
- Default: `30`

The interval in seconds after which **ONE** more login request is possible globally.

### log_to_syslog

- Key: `log_to_syslog`
- Type: `bool`
- Default: `true`

Whether **failed** login requests during **active** rate-limiting should be logged to the syslog.

### syslog_daemon

- Key: `syslog_daemon`
- Type: `non-empty-string`
- Default: `snicco_fortress`

The `syslog_deamon` option will be prepended to all messages that Fortress logs to the syslog.

### syslog_flags

- Key: `syslog_flags`
- Type: `positiv-interger`
- Default: `1` `LOG_PID`

A bitmask, valid options can be found in the [`openlog`](https://www.php.net/manual/de/function.openlog.php) manual.

### syslog_facility

- Key: `syslog_facility`
- Type: `positive-integer`
- Default: `32` `LOG_AUTH`

A bitmask, valid options can be found in the [`openlog`](https://www.php.net/manual/de/function.openlog.php) manual.

---

Next: [The Fortress password module](../modules/password/readme.md).

## Vaults & Pillars module

- JSON namespace: `"vaults_and_pillars"`

### strict_option_vaults_and_pillars

- Key: `strict_option_vaults_and_pillars`
- Type: `boolean`
- Default: `false`

The `strict_option_vaults_and_pillars` option can be used to enable
the [`Vaults & Pillars` `Strict Mode`](../modules/vaults_and_pillars/wordpress_options.md#strict-mode-in-vaults-and-pillars)
for WordPress options.

### option_pillars

- Key: `option_pillars`
- Type: `object`
- Default: `{}`

The `option_pillars` option defines all [Pillars](../modules/vaults_and_pillars/wordpress_options.md#pillars) for
WordPress Options.

The exact required structure is documented [here](../modules/vaults_and_pillars/wordpress_options.md#setting-up-pillars)
and in the [Fortress schema.json](schema.json):

```json
{
  "option_pillars": {
    "type": "object",
    "default": {},
    "patternProperties": {
      "^.*$": {
        "type": "object",
        "properties": {
          "expand_key": {
            "type": "boolean",
            "default": true
          },
          "value": {
            "anyOf": [
              {
                "type": "string"
              },
              {
                "type": "array"
              },
              {
                "type": "object"
              }
            ]
          },
          "env_var": {
            "type": "string"
          }
        },
        "additionalProperties": false,
        "oneOf": [
          {
            "required": [
              "value"
            ]
          },
          {
            "required": [
              "env_var"
            ]
          }
        ]
      }
    }
  }
}
```

### option_vaults

- Key: `option_vaults`
- Type: `object`
- Default: `{}`

The `option_vaults` option defines all [Vaults](../modules/vaults_and_pillars/wordpress_options.md#vaults) for WordPress
Options.

The exact required structure is documented [here](../modules/vaults_and_pillars/wordpress_options.md#setting-up-vaults)
and in the [Fortress schema.json](schema.json):

```json
{
  "option_vaults": {
    "type": "object",
    "default": {},
    "patternProperties": {
      "^.*$": {
        "type": "object",
        "properties": {
          "expand_key": {
            "type": "boolean",
            "default": true
          }
        },
        "additionalProperties": false
      }
    }
  }
}
```

## Code Freeze module

- JSON namespace: `"code_freeze"`

### enabled

- Key: `enabled`
- Type: `"auto"|"yes"|"no"`
- Default: `"auto"`

The `code_freeze.enabled` option controls whether the [Code Freeze](../modules/code_freeze/readme.md) module activated.

- `"auto"` means that the module is only active when the WP_ENVIRONMENT_TYPE constant is set to `production`.
  If the constant is not set, WordPress defaults to `production`.
  Read more
  here: [wp_get_environment_type](https://developer.wordpress.org/reference/functions/wp_get_environment_type/) and
  here [WordPress environment types](https://make.wordpress.org/core/2020/08/27/wordpress-environment-types/).

- `"yes"` means that the module is always active.

- `"no"` means that the module is never active.
