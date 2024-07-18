# Short-circuiting Fortress

<!-- TOC -->
  * [Disabling specific modules](#disabling-specific-modules)
    * [Disabling the password module](#disabling-the-password-module)
    * [Disabling the Vaults & Pillars module](#disable-the-vaults--pillars-module)
  * [Completely disabling/removing Fortress](#completely-disablingremoving-fortress)
<!-- TOC -->

## Disabling specific modules

Each of Fortress's [modules](../readme.md#modules) can be used independently.

To temporarily disable a module remove it from the [`modules` option](../configuration/02_configuration_reference.md#modules).

### Disabling the password module

Important: If for whatever reason you need to disable the Fortress password module **AND you are not currently logged in** do the following:

1. Disable the password module by removing it from the configuration.
2. Reset the password hash for your account by running:<br><br>
    ```shell
    wp user reset-password your-username
    ```
3. Reset your password using the link emailed to your inbox.
4. Do whatever troubleshooting it is that you have to do.
5. Enable Fortress again by removing the constant.
6. **Only** if you [`disabled legacy hashes`](../modules/password/password-hashing.md#disallowing-legacy-hashes):<br><br>
    ```shell
    wp user update <your-username> --user_pass=your-old-password
    ```

These steps are needed because [Fortress password hashes only work when Fortress is enabled](../modules/password/password-hashing.md#migrating-out-hashes).

### Disable the Vaults & Pillars module

Important: If for whatever reason, you need to disable the `Vaults & Pillars` you **MUST**
[unseal all your `Vaults/Pillars`](../modules/vaults_and_pillars/wordpress_options.md#removing-all-vaults-and-pillars). 

Otherwise, upon deactivating the `Vaults & Pillars` module, WordPress will not receive the real
option values anymore since the `Vaults & Pillars` translation layer is missing.

## Completely disabling/removing Fortress

Fortress can be preventing from doing anything by defining the following constant in the wp-config file (or any other file that is loaded before mu-plugins).

```php
define('SNICCO_FORTRESS_LOADED', true);
```

⚠️ ⚠️ ⚠️ 

If you short-circuit or remove Fortress,
you **MUST** follow all the documented steps in the [Disabling Specific Modules](#disabling-specific-modules) section.

--- 

Next: [Error handling in Fortress](error-handling.md).