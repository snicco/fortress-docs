# Fortress and staging sites

<!-- TOC -->
  * [Optimal setup](#optimal-setup)
  * [Alternative if you don't control staging scripts](#alternative-if-you-dont-control-staging-scripts)
    * [Advantages](#advantages)
    * [Disadvantages](#disadvantages)
<!-- TOC -->

---

## Optimal setup

If you use a staging site and push your production database to the staging site, ensure the following:

- Keep Fortress's configuration (`fortress/config.json`, by default) in sync between staging and production.
- Keep Fortress's secrets (`fortress/secrets.php`, by default) in sync between staging and production. Otherwise,
  Fortress will not be able to decrypt data that was encrypted on the production site.
- Don't keep the `fortress/var` directory in sync between staging and production. However, you must create
  the `fortress/var/cache` and `fortress/var/log` directories **once** on the staging site.

## Alternative if you don't control staging scripts

If you are using Fortress on a hosting stack where you have no control over the staging scripts and your hosting
provider does not integrate directly with Fortress, follow these steps for a **one-time setup** on the staging site:

1. Set up the [Fortress home directories](../02_installation.md#create-a-fortress-home-directory) on the staging site.
   Instead of generating new secrets, copy the secrets from the production site (`fortress/secrets.php`) to the staging
   site's `fortress/secrets.php`.
2. Change the location of Fortress's config from `fortress/config.json`
   to `/path-to-mu-plugins/snicco-fortress/config.php` and adjust
   your [Fortress loader](../02_installation.md#create-a-fortress-loader-and-activate-fortress) on both production and
   staging to point Fortress to the new config location.
   <br>  
   Update this line:
    ```php
    \define('SNICCO_FORTRESS_CONFIG_SOURCES', [
        'server' => [
            'path' => '/etc/fortress/server.json',
            'shared_between_sites' => true,
        ],
        'site' => [
            'path' => __DIR__.'/snicco-fortress/config.php',
        ],
        'site.staging' => [
            'path' => __DIR__.'/snicco-fortress/config.staging.php',
            'environment' => 'staging',
        ],
    ]);
    ```
   Note that the file extension is now `.php` instead of `.json`. This ensures that your web server won't serve the file
   as plain text. Fortress supports both JSON and PHP files
   for [configuration](../../configuration/01_how_to_configure_fortress.md).
3. Set the permissions of `config.php` and `config.staging.php` to `600 on` your staging and production site:
    ```shell
    chmod 600 /path-to-mu-plugins/snicco-fortress/config.php /path-to-mu-plugins/snicco-fortress/config.staging.php
    ```
    - Replace `/path-to-mu-plugins/snicco-fortress/config.php` with the path to your site's mu-plugins directory.
    - Replace `/path-to-mu-plugins/snicco-fortress/config.staging.php` with the path to your site's mu-plugins directory.
4. If your staging sites are on entirely different server, you should remove the `server` configuration source entirely, or copy it to your staging server.

### Advantages

- All staging/production scripts will work without any manual intervention.

### Disadvantages

The Fortress [configuration options](../../configuration/02_configuration_reference.md) are **not** as sensitive as cryptographic secrets, but they might contain information
about your security settings that might be sensitive depending on your threat model.

- The `config.php` file is now stored inside the web root, increasing its attack surface.
  - It might be included in backups, which could be insecurely stored (common with local backup plugins).
  - If your web server is misconfigured, it might serve the contents of the file as plain text.
- The scripts of your hosting provider might overwrite the permissions of the `config.php` file to something less restrictive.
- Any "fix permissions" tools/plugins might reset the permissions of the `config.php` file to something less restrictive.
  
