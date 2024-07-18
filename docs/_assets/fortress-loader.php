<?php

declare(strict_types=1);

/*
 * Plugin Name:       Fortress
 * Plugin URI:        https://fortress.snicco.io
 * Description:       Server Integrated Application Security for WordPress.
 * Requires at least: 6.0.0
 * Requires PHP:      7.4
 * Author:            Snicco
 * Author URI:        https://snicco.io
 * License:           Commercial
 * Text Domain:       snicco-fortress
 */

/*
|--------------------------------------------------------------------------
| Bootstraps Fortress as a must-use plugin according to the official
| Fortress docs.
|--------------------------------------------------------------------------
|
| This loader file assumes that the log/cache directories and the
| secrets.php file are located in a directory called "fortress"
| which is one level above the WordPress files.
|
| This file becomes part of YOUR codebase, you can modify it as you see fit.
| Fortress will never change your copy of this file.
|
| You don't need to use the file structure from the documentation at all.
| Fortress only looks at the values of the constants defined below.
| But unless you have a good reason to, don't deviate from the defaults.
|
*/
\call_user_func(function (): void {
    $vhost_directory = \rtrim(\dirname(ABSPATH), '/');
    $fortress_directory = $vhost_directory . '/fortress';
    /*
    |--------------------------------------------------------------------------
    | Check if the Fortress secrets exist
    |--------------------------------------------------------------------------
    |
    | This is a safety check to prevent a site owner from accidentally crashing
    | their site if they migrate the site to an external staging tool/plugin.
    |
    | If you're using Fortress in a tightly controlled environment
    | where you can treat a missing directory as a fatal error, uncomment
    | this condition below and Fortress will throw an exception if secrets are missing.
    |
    */
    if (! \is_file("{$fortress_directory}/secrets.php")) {
        $msg = \sprintf(
            "Incomplete Fortress installation detected: A Fortress loader was included from '%s' but the expected Fortress secrets does not exist at '%s'.\n\tDid you maybe migrate your site to a different hosting company and forgot to remove Fortress?",
            __FILE__,
            "{$fortress_directory}/secrets.php"
        );

        \trigger_error($msg, E_USER_WARNING);

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Define all required secrets
    |--------------------------------------------------------------------------
    |
    */
    require_once "{$fortress_directory}/secrets.php";

    /*
    |--------------------------------------------------------------------------
    | Point Fortress to the correct directories
    |--------------------------------------------------------------------------
    |
    */
    \define('SNICCO_FORTRESS_CACHE_DIR', "{$fortress_directory}/var/cache");
    \define('SNICCO_FORTRESS_LOG_DIR', "{$fortress_directory}/var/log");

    /*
    |--------------------------------------------------------------------------
    | Point Fortress to configuration files
    |--------------------------------------------------------------------------
    |
    | Neither the site config file nor the server config file have to exist,
    | but it makes sense to define their locations upfront.
    |
    | "wp fort config update" will create the files as needed.
    |
    */
    \define('SNICCO_FORTRESS_SITE_CONFIG_FILE', "{$fortress_directory}/config.json");
    \define('SNICCO_FORTRESS_SERVER_CONFIG_FILE', '/etc/fortress/server.json');

    /*
    |--------------------------------------------------------------------------
    | Turn on the lights
    |--------------------------------------------------------------------------
    |
    | Including main.php will boot Fortress.
    | Everything needs to be configured at this point.
    |
    */
    require_once __DIR__ . '/snicco-fortress/releases/current/main.php';
});
