# Using a custom directory structure

The Fortress [installation guide](../02_installation.md) is optimized for the most common WordPress setups of a single
server hosting multiple sites.

However, no part of the chosen directory structure is required to run Fortress.

Fortress only relies on a few PHP constants. The default Fortress loader from the installation guide includes these
constants to configure the necessary directories and files.

Fortress is initialized by including the `main.php` file.
If you define all required constants before
loading `main.php`, Fortress will work with any directory structure.

However, it's important to follow the principles that lead to the directory structure in the installation guide to
ensure optimal
security.

```php
/*
|--------------------------------------------------------------------------
| Define all required secrets
|--------------------------------------------------------------------------
|
|
*/
require_once "$fortress_directory/secrets.php";

/*
|--------------------------------------------------------------------------
| Point Fortress to the correct directories
|--------------------------------------------------------------------------
|
*/
define('SNICCO_FORTRESS_CACHE_DIR', "$fortress_directory/var/cache");
define('SNICCO_FORTRESS_LOG_DIR', "$fortress_directory/var/log");

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
define('SNICCO_FORTRESS_SITE_CONFIG_FILE', "$fortress_directory/config.json");
// Optional, define a server-wide configuration file.
define('SNICCO_FORTRESS_SERVER_CONFIG_FILE', '/etc/fortress/server.json');

/*
|--------------------------------------------------------------------------
| Turn on the lights
|--------------------------------------------------------------------------
|
| Including main.php will boot Fortress.
| Everything needs to be configured at this point.
|
*/
require_once __DIR__.'/snicco-fortress/releases/current/main.php';
```
