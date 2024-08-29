# Fortress configuration reference

<!-- TOC -->
  * [Overview](#overview)
  * [Root-level](#root-level)
    * [challenges_table_name](#challenges_table_name)
    * [cli_namespace](#cli_namespace)
    * [db_table_namespace](#db_table_namespace)
    * [modules](#modules)
    * [privileged_user_roles](#privileged_user_roles)
    * [theme_css_file](#theme_css_file)
    * [url_namespace](#url_namespace)
  * [Auth module](#auth-module)
    * [magic_link_allow_requesting_via_http](#magic_link_allow_requesting_via_http)
    * [magic_link_show_on_wp_login_form](#magic_link_show_on_wp_login_form)
    * [max_totp_attempts_before_lockout](#max_totp_attempts_before_lockout)
    * [require_2fa_for_roles](#require_2fa_for_roles)
    * [require_2fa_for_roles_before_login](#require_2fa_for_roles_before_login)
    * [skip_2fa_setup_duration_seconds](#skip_2fa_setup_duration_seconds)
    * [totp_recovery_codes_locked_to_user_id](#totp_recovery_codes_locked_to_user_id)
    * [totp_secrets_table_name](#totp_secrets_table_name)
    * [totp_sha_algo](#totp_sha_algo)
  * [Code Freeze module](#code-freeze-module)
    * [enabled](#enabled)
  * [Password module](#password-module)
    * [allow_legacy_hashes](#allow_legacy_hashes)
    * [allow_legacy_hashes_for_non_passwords](#allow_legacy_hashes_for_non_passwords)
    * [auto_upgrade_hashes](#auto_upgrade_hashes)
    * [default_hash_strength](#default_hash_strength)
    * [disable_application_passwords](#disable_application_passwords)
    * [disable_web_password_reset_for_roles](#disable_web_password_reset_for_roles)
    * [password_policy_excluded_roles](#password_policy_excluded_roles)
    * [include_pluggable_functions](#include_pluggable_functions)
    * [store_hashes_encrypted](#store_hashes_encrypted)
  * [Rate limit module](#rate-limit-module)
    * [cache_group](#cache_group)
    * [device_id_burst](#device_id_burst)
    * [device_id_cookie_prefix](#device_id_cookie_prefix)
    * [device_id_refill_one_token_seconds](#device_id_refill_one_token_seconds)
    * [global_burst](#global_burst)
    * [global_refill_one_token_seconds](#global_refill_one_token_seconds)
    * [ip_burst](#ip_burst)
    * [ip_refill_one_token_seconds](#ip_refill_one_token_seconds)
    * [log_to_syslog](#log_to_syslog)
    * [storage](#storage)
    * [syslog_daemon](#syslog_daemon)
    * [syslog_facility](#syslog_facility)
    * [syslog_flags](#syslog_flags)
    * [use_hashed_ips](#use_hashed_ips)
    * [username_burst](#username_burst)
    * [username_refill_one_token_seconds](#username_refill_one_token_seconds)
  * [Session module](#session-module)
    * [absolute_timeout](#absolute_timeout)
    * [absolute_timeout_per_cap](#absolute_timeout_per_cap)
    * [absolute_timeout_remembered_user](#absolute_timeout_remembered_user)
    * [absolute_timeout_remembered_user_per_cap](#absolute_timeout_remembered_user_per_cap)
    * [disable_rotation_for_ajax_like_requests_per_cap](#disable_rotation_for_ajax_like_requests_per_cap)
    * [idle_timeout](#idle_timeout)
    * [idle_timeout_per_cap](#idle_timeout_per_cap)
    * [non_sudo_mode_recheck_frequency](#non_sudo_mode_recheck_frequency)
    * [protected_capabilities](#protected_capabilities)
    * [protected_pages](#protected_pages)
    * [remember_cookie_name](#remember_cookie_name)
    * [rotation_timeout](#rotation_timeout)
    * [rotation_timeout_per_cap](#rotation_timeout_per_cap)
    * [sudo_mode_timeout](#sudo_mode_timeout)
    * [sudo_mode_timeout_per_cap](#sudo_mode_timeout_per_cap)
    * [table_name](#table_name)
  * [Vaults and Pillars module](#vaults-and-pillars-module)
    * [option_pillars](#option_pillars)
    * [option_vaults](#option_vaults)
    * [strict_option_vaults_and_pillars](#strict_option_vaults_and_pillars)
<!-- TOC -->

## Overview

This document contains a reference to all
available configuration options in Fortress; what they mean; and what
their default values are.

A JSON schema is available [here](schema.json)
to provide syntax highlighting, auto-completion, and validation in your favorite IDE:

- [PHPStorm: How to add custom schema sources.](https://www.jetbrains.com/help/phpstorm/json.html#ws_json_schema_add_custom)
- [Visual Studio Code: How to add custom schema sources](https://code.visualstudio.com/docs/languages/json).

Below is the JSON representation of
the Fortress baseline configuration
for a (default) WordPress site:

(The configuration for WordPress multisite is slightly different)

```json
{
  "challenges_table_name": null,
  "cli_namespace": "fort",
  "db_table_namespace": "snicco_fortress",
  "modules": [
    "password",
    "session",
    "auth",
    "rate_limit",
    "vaults_and_pillars",
    "code_freeze"
  ],
  "privileged_user_roles": [
    "administrator",
    "editor"
  ],
  "theme_css_file": null,
  "url_namespace": "/snicco-fortress",
  "auth": {
    "magic_link_allow_requesting_via_http": true,
    "magic_link_show_on_wp_login_form": true,
    "max_totp_attempts_before_lockout": 5,
    "require_2fa_for_roles": [
      "administrator",
      "editor"
    ],
    "require_2fa_for_roles_before_login": [],
    "skip_2fa_setup_duration_seconds": 1800,
    "totp_recovery_codes_locked_to_user_id": false,
    "totp_secrets_table_name": null,
    "totp_sha_algo": "sha1"
  },
  "code_freeze": {
    "enabled": "auto"
  },
  "password": {
    "allow_legacy_hashes": true,
    "allow_legacy_hashes_for_non_passwords": true,
    "auto_upgrade_hashes": true,
    "default_hash_strength": "moderate",
    "disable_application_passwords": false,
    "disable_web_password_reset_for_roles": [
      "administrator",
      "editor"
    ],
    "include_pluggable_functions": true,
    "password_policy_excluded_roles": [],
    "store_hashes_encrypted": false
  },
  "rate_limit": {
    "cache_group": null,
    "device_id_burst": 5,
    "device_id_cookie_prefix": "device_id",
    "device_id_refill_one_token_seconds": 20,
    "global_burst": 100,
    "global_refill_one_token_seconds": 30,
    "ip_burst": 20,
    "ip_refill_one_token_seconds": 1800,
    "log_to_syslog": true,
    "storage": "auto",
    "syslog_daemon": "snicco_fortress",
    "syslog_facility": 32,
    "syslog_flags": 1,
    "use_hashed_ips": false,
    "username_burst": 5,
    "username_refill_one_token_seconds": 900
  },
  "session": {
    "absolute_timeout": 43200,
    "absolute_timeout_per_cap": [],
    "absolute_timeout_remembered_user": 86400,
    "absolute_timeout_remembered_user_per_cap": [],
    "disable_rotation_for_ajax_like_requests_per_cap": [],
    "idle_timeout": 1800,
    "idle_timeout_per_cap": [],
    "non_sudo_mode_recheck_frequency": 10,
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
      "edit_user",
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
      "view_site_health_checks",
      "install_languages",
      "edit_comment"
    ],
    "protected_pages": [
      "/wp-admin/update-core.php",
      "/wp-admin/themes.php",
      "/wp-admin/theme-install.php",
      "/wp-admin/plugins.php",
      "/wp-admin/plugin-install.php",
      "/wp-admin/users.php",
      "/wp-admin/user-new.php",
      "/wp-admin/user-edit.php",
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
    "remember_cookie_name": "snicco_fortress_remember_me",
    "rotation_timeout": 1200,
    "rotation_timeout_per_cap": [],
    "sudo_mode_timeout": 600,
    "sudo_mode_timeout_per_cap": [],
    "table_name": null
  },
  "vaults_and_pillars": {
    "option_pillars": [],
    "option_vaults": [],
    "strict_option_vaults_and_pillars": false
  }
}
```

## Root-level

The top-level namespace contains configuration
options that apply to Fortress as a whole.

### challenges_table_name

- Type: `null|non-empty-string`
- Default: `null`

Controls the name of the database table where Fortress
stores cryptographic challenges.

🚨 This option is deprecated and will be removed in the next major release of Fortress.

### cli_namespace

- Type: `non-empty-string`
- Default: `fort`

Controls WP-CLI namespace that will
be used to register Fortress's CLI with the `wp` command.

### db_table_namespace

- Type: `non-empty-string`
- Default: `snicco_fortress`

Controls the prefix that will be used for all database tables
created by Fortress.

This value must *not* include the WordPress table prefix.

### modules

- Type: `non-empty-string[]`
- Default: `["password","session","auth","rate_limit", "vaults_and_pillars", "code_freeze"]`
- Allowed values: `["password","session","auth","rate_limit", "vaults_and_pillars", "code_freeze"]`

Controls which [Fortress modules](../readme.md#modules) are activated.

Unless you know what you're doing, it's best to leave this option as-is.

### privileged_user_roles

- Type: `non-empty-string[]`
- Default: `["administrator","editor"]`
- Allowed values: Any valid WordPress user role.

Controls which user roles are considered "highly privileged" by Fortress
to apply more strict security controls.
Several other options inherit from it by default.

### theme_css_file

- Type: `non-empty-string`
- Default: `null`

This option can be used to provide a relative path to a custom CSS file
that will be loaded on all Fortress pages.

The CSS file will be loaded after Fortress's default
templates, allowing you to override any styles, especially
the CSS variables that Fortress uses.

The default CSS of Fortress is below:

```css
:root {
    --color-primary-light: 99 102 241;
    --color-primary: 79 70 229;
    --color-primary-dark: 67 56 202;
}
```

To give Fortress's UI a "neon-green" theme:

```css
:root {
    --color-primary-light: 163 230 53;
    --color-primary: 101 163 13;
    --color-primary-dark: 77 124 15;
}
```

### url_namespace

- Type: `non-empty-string`
- Default: `/snicco-fortress`

Controls the shared URL prefix of all Fortress routes.

## Auth module

- Configuration namespace: `"auth"`

### magic_link_allow_requesting_via_http

- Type: `bool`
- Default: `true`

Controls whether Fortress's magic links
can be requested via the web UI.

If set to `false`, magic links can only be requested via
the Fortress CLI.

### magic_link_show_on_wp_login_form

- Type: `bool`
- Default: `true`

Controls whether Fortress shows a link to the custom magic link login page
on the default wp-login.php page.

If set to `false`, the link will not be shown, however,
the magic link login page will still be accessible.

This option is ignored if [`magic_link_allow_requesting_via_http`](#magic_link_allow_requesting_via_http) is set
to `false`.

### max_totp_attempts_before_lockout

- Type: `positive-integer`
- Default: `5`

Controls how many failed 2FA attempts Fortress will allow
before [locking an account](../modules/auth/2fa_totp.md#rate-limiting-2fa-attempts).

### require_2fa_for_roles

- Type: `non-empty-string[]`
- Default: [`privileged_user_roles`](#privileged_user_roles)
- Allowed values: Any valid WordPress user role.

Controls which user roles are [required to set up 2FA immediately after
logging in](../modules/auth/2fa_totp.md#force-setup-screen).

### require_2fa_for_roles_before_login

- Type: `non-empty-string[]`
- Default: `[]` (empty array)
- Allowed values: Any valid WordPress user role.

Controls which user roles must have 2FA enabled
even [before they can log in](../modules/auth/2fa_totp.md#enforcing-2fa-pre-login).

### skip_2fa_setup_duration_seconds

- Type: `positive-integer`
- Default: `1800` (30 minutes)

Controls the duration that 2FA setup can be [skipped](../modules/auth/2fa_totp.md#force-setup-screen) for
any user that has one of the roles defined in
[`require_2fa_for_roles`](#require_2fa_for_roles).

The duration has to be provided in seconds.

### totp_recovery_codes_locked_to_user_id

- Type: `bool`
- Default: `false`

Controls whether a user's 2FA recovery codes
are cryptographically locked with an HMAC to their user ID.

🚨 This option exists for backwards compatability reason.
It has no effect if you started
using Fortress after
version [`1.0.0-beta.36`](https://github.com/snicco/fortress/blob/beta/CHANGELOG.md#100-beta36-2024-03-16).

### totp_secrets_table_name

- Type: `null|non-empty-string`
- Default: `null`

Controls the name of the database table where Fortress
stores users' TOTP secrets.

🚨 This option is deprecated and will be removed in the next major release of Fortress.

### totp_sha_algo

- Type: `"sha1" | "sha256" | "sha512"`
- Default: `"sha1"`
- Allowed values: `"sha1" | "sha256" | "sha512"`

Controls the hash algorithm that Fortress uses to generate
six-digit one-time passwords.

Unless you know what you're doing, don't change this.

The default `"sha1"` value is what's used by most password manager apps
(1Password, Google Authenticator, etc.).

## Code Freeze module

- Configuration namespace: `"code_freeze"`

### enabled

- Type: `"auto" | "yes" | "no"`
- Default: `"auto"`

Controls whether the [Code Freeze](../modules/code_freeze/readme.md#when-is-code-freeze-active) is enabled.

The value of `"auto"` means that Fortress will automatically enable Code Freeze
if WordPress runs in production, and disable it in development/staging/local
environments.

## Password module

- Configuration namespace: `"password"`

### allow_legacy_hashes

- Type: `bool`
- Default: `true`

Controls whether Fortress allows users
with non-upgraded legacy hashes to log in.

This option can be safely set to `false`
after all users have logged in at least once,
or if
you [upgraded all hashes via Fortress CLI](../modules/password/password-hashing.md#securing-existing-user-passwords).

### allow_legacy_hashes_for_non_passwords

- Type: `bool`
- Default: `true`

Controls where Fortress allows legacy hashes
for non-passwords.

A non-password is anything that is passed into `wp_verify_password`
that is not a password.

This option should only be used if you're building
a new site with Fortress since there is no way
to automatically upgrade non-password hashes.

### auto_upgrade_hashes

- Type: `bool`
- Default: `true`

Controls whether a user's password will automatically be upgraded
to Fortress's secure hashing scheme after logging in.

The user's password stays the same—only the hash is upgraded.

### default_hash_strength

- Type: `"interactive" | "moderate" | "sensitive"`
- Default: `"moderate"`

Controls how long the libsodium PHP extension will take
to compute the hash of a password.

If you have a powerful server, you might want to set this to `"sensitive"`.

### disable_application_passwords

- Type: `bool`
- Default: `false`

Controls whether Fortress will completely disable application
passwords.

If your site does not use application passwords, it's best
to disable this option because site owners can easily
be tricked into created malicious application passwords.

### disable_web_password_reset_for_roles

- Type: `non-empty-string[]`
- Default: `["administrator","editor"]`
- Allowed values: Any valid WordPress user role.

Controls which user roles will not be able
to reset their password via the web UI / frontend.

Refer to the [password reset documentation](../modules/password/disabling-password-resets-for-privileged-users.md)
for more information.

### password_policy_excluded_roles

- Type: `non-empty-string[]`
- Default: `[]` (empty array)
- Allowed values: Any valid WordPress user role.

Controls which user roles are excluded from the Fortress
[password policy](../modules/password/password-policy.md).

### include_pluggable_functions

- Type: `bool`
- Default: `true`

Controls whether Fortress takes over the WordPress
password hashing functions.

If you set this option to false, you can still
use other functionality of the password module
such as the password policies, or password reset restrictions.

### store_hashes_encrypted

- Type: `bool`
- Default: `false`

Controls whether the secure password hashes will be stored
encrypted on top of being hashed to provide additional
security benefits.

This was previously enabled by default, but is now
disabled because it requires more than 255 characters
to store the final string, which required modification to the
`wp_users.user_pass` column.

That's not necessarily a problem, but some plugins attempt
to reset the user_pass column to 255 characters and thus
causing data truncation.

If you use Fortress on a tightly controlled stack, you can
enable this option.

You can at any point switch between encrypted and non-encrypted.

Refer to
the [password hashing documentation](../modules/password/password-hashing.md#argon2id--authenticated-encryption) for
more information.

## Rate limit module

- Configuration namespace: `"rate_limit"`

### cache_group

- Type: `null|non-empty-string`
- Default: `null`

Controls the object cache group that Fortress uses
to store rate limit data.

🚨 This option is deprecated and will be removed in the next major release of Fortress.

### device_id_burst

- Type: `positive-integer`
- Default: `5`

Controls the maximum number of requests that can be made
from a single [device ID](../modules/ratelimit/login-throttling.md#the-device-id-system)
before login throttling is applied.

### device_id_cookie_prefix

- Type: `non-empty-string`
- Default: `"device_id"`

Controls the prefix that Fortress uses to set the device ID
cookie.

### device_id_refill_one_token_seconds

- Type: `positive-integer`
- Default: `20`

Controls the interval in seconds
that a user rate-limited by his device ID has to wait
before he can make one more login request.

With a value of `20`, in the long run, a user can make a request every 20 seconds.

Refer to the [implementation details](../modules/ratelimit/implementation.md)
to learn more about the relation between [`device_id_burst`](#device_id_burst),
[`device_id_refill_one_token_seconds`](#device_id_refill_one_token_seconds),

### global_burst

- Type: `positive-integer`
- Default: `100`

Controls the maximum number of login requests that can be made
globally before login throttling is activated
for users without device IDs.

### global_refill_one_token_seconds

- Type: `positive-integer`
- Default: `30`

Controls the interval in seconds
that has to pass before another login request can be made
for users without device IDs.

With a value of `30`, in the long run, there can only be one login request every 30 seconds.

This does not apply to users with device IDs.

### ip_burst

- Type: `positive-integer`
- Default: `20`

Controls the maximum number of requests that can be made
from a single IP address before login throttling is applied.

### ip_refill_one_token_seconds

- Type: `positive-integer`
- Default: `1800` (30 minutes)

Controls the interval in seconds
that a user rate-limited by his IP address has to wait
before he can make one more login request.

With a value of `1800`, in the long run, a user **without device id** can make a login request every 30 minutes.

### log_to_syslog

- Type: `bool`
- Default: `true`

Controls whether Fortress will also log request to
don't respect active throttling to the syslog.

It can be used to [integrate with Fail2Ban](../modules/ratelimit/login-throttling.md#fail2ban-integration).

### storage

- Type: `"auto" | "database"`
- Default: `"auto"`

If set to `"auth"`, Fortress will store rate limit data
in the object cache if it's persistent, and in the database
if it's not.

If set to `"database"`, Fortress will always store rate limit data
in the database.

### syslog_daemon

- Type: `non-empty-string`
- Default: `"snicco_fortress"`

Controls the prefix that will be prepended to all messages that Fortress logs to the syslog.

### syslog_facility

- Type: `positive-integer`
- Default: `32` (`LOG_AUTH`)

A bitmask, valid options can be found in the [`openlog`](https://www.php.net/manual/de/function.openlog.php) manual.

### syslog_flags

- Type: `positiv-interger`
- Default: `1` (`LOG_PID`)

A bitmask, valid options can be found in the [`openlog`](https://www.php.net/manual/de/function.openlog.php) manual.

### use_hashed_ips

- Type: `bool`
- Default: `false`

Controls whether Fortress will hash the IP address
before storing it in the database.

🚨 This option is deprecated and will be removed in the next major release of Fortress.

### username_burst

- Type: `positive-integer`
- Default: `5`

Controls the maximum number of requests that can be made
from a single username before login throttling is applied.

### username_refill_one_token_seconds

- Type: `positive-integer`
- Default: `900` (15 minutes)

Controls the interval in seconds that
has to pass until another login attempt can be made
for a given username.

With a value of `900`, in the long run, there
can only be one login request for a given username every 15 minutes.

## Session module

- JSON namespace: `"session"`

### absolute_timeout

- Type: `positive-interger`
- Default: `43200` (12 hours)

Controls the maximum duration in seconds of a user's session.
User activity cannot extend this timeout.

This value only applies to users that dit not check the "remember_me" option during login!

For users that checked the "remember_me" option during login, the value
of [`absolute_timeout_remembered_user`](#absolute_timeout_remembered_user) applies.

### absolute_timeout_per_cap

- Type: `array<string,positive-interger`
- Default: `[]`
- Allowed values: Any valid WordPress user capability or role.

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

This option only applies to users that did not check the "remember_me" option during login.
For users that checked the "remember_me" option during login, the value
of [`absolute_timeout_remembered_user_per_cap`](#absolute_timeout_remembered_user_per_cap) applies.

### absolute_timeout_remembered_user

- Type: `positive-interger`
- Default: `86400` (24 hours)

Controls the maximum duration in seconds of a user's session
that checked the "remember_me" option during login.

### absolute_timeout_remembered_user_per_cap

- Type: `array<string,positive-interger`
- Default: `[]`
- Allowed values: Any valid WordPress user capability or role.

This option is the same
as [`absolute_timeout_per_cap`](#absolute_timeout_per_cap) but applies to users that checked the "remember_me"
option during login.

### disable_rotation_for_ajax_like_requests_per_cap

- Type: `non-empty-string[]`
- Default: `[]`
- Allowed values: Any valid WordPress user capability or role.

This option can be used
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

### idle_timeout

- Type: `positive-interger`
- Default: `1800` (30 minutes)

Controls the timeout in seconds after which a user without
any activity is logged out.

### idle_timeout_per_cap

- Type: `array<string,positive-interger`
- Default: `[]`
- Allowed values: Any valid WordPress user capability or role.

This option can be used if more fine-grained control of
the [idle timeout](../modules/session/session-managment-and-security.md#the-idle-timeout) is needed
for different user roles.

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

### non_sudo_mode_recheck_frequency

- Type: `positive-integer`
- Default: `10`

Controls how often Fortress will make an ajax-request to the backend to check if a user's session has re-entered
the [sudo mode](../modules/session/sudo-mode.md).

This timeout is only relevant if the user is either about to cross the threshold of not being in sudo mode anymore,
or has crossed the sudo timeout already.

A value of `10` means that Fortress would check every ten **seconds** if the user has confirmed his credentials
AFTER his session had already crossed the sudo timeout.

No checks are made if the user is still in sudo mode.

### protected_capabilities

- Type: `non-empty-string[]`
- Default:
  ```json
  [
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
      "edit_user",
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
      "view_site_health_checks",
      "install_languages",
      "edit_comment"
    ]
  ```

Represents a list of capabilities that will be removed
from a user's session if they're not in [sudo mode](../modules/session/sudo-mode.md#protected-capabilities) anymore.

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

For multisite, the following capabilities
are also protected:

```json
[
  "create_sites",
  "delete_sites",
  "manage_network",
  "manage_sites",
  "manage_network_users",
  "manage_network_plugins",
  "manage_network_themes",
  "manage_network_options",
  "upgrade_network",
  "setup_network"
]
```

### protected_pages

- Type: `non-empty-string[]`
- Default:
  ```json
  [
    "/wp-admin/update-core.php",
    "/wp-admin/themes.php",
    "/wp-admin/theme-install.php",
    "/wp-admin/plugins.php",
    "/wp-admin/plugin-install.php",
    "/wp-admin/users.php",
    "/wp-admin/user-new.php",
    "/wp-admin/user-edit.php",
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
  ]
  ```

  Additionally, for multisite:

  ```json
  [
    "/wp-admin/network.php",
    "/wp-admin/ms-admin.php",
    "/wp-admin/ms-delete-site.php",
    "/wp-admin/ms-edit.php",
    "/wp-admin/ms-options.php",
    "/wp-admin/ms-sites.php",
    "/wp-admin/ms-themes.php",
    "/wp-admin/ms-upgrade-network.php",
    "/wp-admin/network/*"
  ]
  ```
  The `/wp-admin` prefix is determined dynamically.<br>If you're using a Bedrock site, it would be `/wp/wp-admin`.

Control's URL paths that a user can only access if his session is still in sudo mode.

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

Refer to the sudo mode documentation for the 
[difference between protected_pages and protected_capabilities](../modules/session/sudo-mode.md#distinguishing-between-protected-capabilities--protected-pages).

### remember_cookie_name

- Type: `non-empty-string`
- Default: `"snicco_fortress_remember_me"`

Controls the cookie name that Fortress uses to determine
if a user wanted to be remembered.

### rotation_timeout

- Type: `positive-interger`
- Default: `1200` (20 minutes)

The `rotation_timeout` is the interval after which the user's session token
is [rotated](../modules/session/session-managment-and-security.md#the-rotation-timeout).

### rotation_timeout_per_cap

- Type: `array<string,positive-interger`
- Default: `[]`
- Allowed values: Any valid WordPress user capability or role.

This option can be used if more fine-grained control of the rotation timeout is needed
per user role or capability.

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

### sudo_mode_timeout

- Type: `positive-integer`
- Default: `600` (10 minutes)

Controls the interval in seconds during which Fortress will consider a session to be
in [sudo mode](../modules/session/sudo-mode.md) after
logging in.

### sudo_mode_timeout_per_cap

- Type: `array<string,positive-interger`
- Default: `[]`
- Allowed values: Any valid WordPress user capability or role.

This option can be used if more fine-grained control of the sudo mode timeout is needed
per user role or capability.

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

### table_name

- Type: `null|non-empty-string`
- Default: `null`

Controls the name of the database table where Fortress
stores user sessions.

🚨 This option is deprecated and will be removed in the next major release of Fortress.

## Vaults and Pillars module

- Configuration namespace: `"vaults_and_pillars"`

### option_pillars

- Type: `array<non-empty-string,array>`
- Default: `{}`

Defines all [Pillars](../modules/vaults_and_pillars/wordpress_options.md#pillars) for
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

- Type: `array<non-empty-string,array>`
- Default: `{}`

Defines all [Vaults](../modules/vaults_and_pillars/wordpress_options.md#vaults) for WordPress Options.

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

### strict_option_vaults_and_pillars

- Type: `boolean`
- Default: `false`

Controls whether the [Vaults & Pillars Strict Mode](../modules/vaults_and_pillars/wordpress_options.md#strict-mode-in-vaults-and-pillars)
is enabled for WordPress options.

