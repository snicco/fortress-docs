# The Fortress password module

<!-- TOC -->
* [Main Features](#main-features)
    * [Secure password hashing](#secure-password-hashing)
    * [Password policy](#password-policy)
    * [Disabling password resets for privileged users](#disabling-password-resets-for-privileged-users)
* [Tweaks](#tweaks)
    * [Disable application passwords](#disable-application-passwords)
    * [Decrease password reset link duration](#decrease-password-reset-link-duration)
    * [Destroy all user sessions on password change](#destroy-all-user-sessions-on-password-change)
<!-- TOC -->

## Main Features

### Secure password hashing

- [Password hashing documentation](password-hashing.md).

### Password policy

- [Password policy documentation](password-policy.md).

### Disabling password resets for privileged users

- [Disable password resets documentation](disabling-password-resets-for-privileged-users.md).

## Tweaks

### Disable application passwords

Fortress can completely disable WordPress application passwords, as most sites do not use them.

They are vulnerable to social engineering attacks.
An attacker can trick an unsuspected site admin into adding a new application password by clicking on a link.

You can disable application passwords 
by setting the [`disable_application_passwords`](../../configuration/02_configuration_reference.md#disable_application_passwords) option to `true`.

Alternatively, 
running the [`wp fort config optimize`](../../configuration/01_how_to_configure_fortress.md#automatically-optimize-the-configuration)
will disable application passwords automatically if no user on the site has an application password
in use.

### Decrease password reset link duration

By default, WordPress password reset links are valid for 24 hours.

Fortress decreases this duration to 30 minutes.

Should this be too strict, you can hook into the `password_reset_expiration` hook with a priority greater than 10.

```php
// Change duration to one hour.
add_filter('password_reset_expiration', fn (): int => 60 * 60, 11);
```

### Destroy all user sessions on password change

WordPress invalidates a user's sessions after a password change by coincidence because a user's password hash happens to be part of his authentication cookie.
To be precise, [the 8th - 12th characters of the password hash are part of a user's auth cookie](https://github.com/WordPress/wordpress-develop/blob/6.1/src/wp-includes/pluggable.php#L862).

However, the 8th–12th hash characters are not guaranteed to be different,
especially not with password hashing algorithms that store settings as the prefix of a resulting password hash.

For that reason, Fortress explicitly destroys a user's sessions after he changes his password.


