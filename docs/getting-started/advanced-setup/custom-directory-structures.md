# Using a custom directory structure

The Fortress [installation guide](../02_installation.md) is optimized for the most common WordPress setups of a single
server hosting multiple sites.

However, no part of the chosen directory structure is required to run Fortress.

Fortress only reads a few PHP constants. 

The [default Fortress loader](../../_assets/fortress-loader.php) from the installation guide includes these
constants to configure the necessary directories and files.

Fortress is initialized by including the `main.php` file.

If you define all required constants before
loading `main.php`, Fortress will work with any directory structure.

However, it's important to follow the principles that lead to the directory structure in the installation guide to
ensure optimal
security.

Take a look at the [default loader](../../_assets/fortress-loader.php) to see which constants are required.
You can customize the default loader in any way you like, it's part of your codebase.