# The Rate Limiting module

<!-- TOC -->
* [Disclaimer](#disclaimer)
* [Implementation](#implementation-details)
* [Applications](#applications)
    * [Login throttling](#login-throttling)
    * [Password reset throttling](#password-reset-throttling)
<!-- TOC -->

## Disclaimer

Rate limiting in Fortress hardens the security of a website. However, nothing in the rate-limiting module of Fortress is meant to be used for DOS protection, like flooding a server with a massive number of requests.

This is impossible to tackle effectively in the application layer and should be handled at the server or CDN level.

## Implementation details

Before you start diving into the applications of Fortress's rate-limiting module, you might like to dive into
the [implementation details](implementation.md) of our custom Rate Limiter.

## Applications

### Login throttling

- [Documentation](login-throttling.md).

### Password reset throttling

- [Documentation](password-reset-throttling.md).


---

Next: [Implementation details](implementation.md)
