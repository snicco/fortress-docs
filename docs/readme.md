# Fortress developer documentation

<!-- TOC -->
  * [Getting started](#getting-started)
    * [Advanced setup](#advanced-setup)
  * [Configuration](#configuration)
  * [Modules](#modules)
  * [Fortress CLI](#fortress-cli)
  * [Debugging and more](#debugging-and-more)
<!-- TOC -->

---

## Getting started

1. [Requirements](getting-started/01_requirements.md)
2. [Installation](getting-started/02_installation.md)
3. [Updates](getting-started/03_updates.md)

### Advanced setup

1. [Fortress and staging sites](getting-started/advanced-setup/staging-sites.md)
2. [Secret management](getting-started/advanced-setup/secret-managment.md)
3. [Custom directory structures](getting-started/advanced-setup/custom-directory-structures.md)

## Configuration

1. [How to configure Fortress](configuration/01_how_to_configure_fortress.md)
2. [Complete configuration reference](configuration/02_configuration_reference.md)

## Modules

Fortress consists of six independent modules that you can use independently of each other; all six modules
are enabled by default.

1. [Password Security](modules/password/readme.md)
2. [Rate limiting](modules/ratelimit/readme.md)
3. [Authentication](modules/auth/readme.md)
4. [Session Management](modules/session/readme.md)
5. [Vaults & Pillars](modules/vaults_and_pillars/readme.md)
6. [Code Freeze](modules/code_freeze/readme.md)

## Fortress CLI

Fortress is built with a CLI-first approach to allow maximum automation. 

Refer to the [complete Fortress CLI reference](cli/readme.md) for more all available commands.

## Debugging and more

- [Short-circuiting Fortress](debugging-and-more/short-circuiting-fortress.md)
- [Error handling in Fortress](debugging-and-more/error-handling.md)
- [Logging in Fortress](debugging-and-more/logging.md)