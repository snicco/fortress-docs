# Secure password hashing

<!-- TOC -->
  * [Fortress password hashing](#fortress-password-hashing)
    * [Argon2id + Blake2b HMAC (default)](#argon2id--blake2b-hmac-default)
      * [Security benefits](#security-benefits)
    * [Argon2id + authenticated encryption](#argon2id--authenticated-encryption)
      * [Security benefits](#security-benefits-1)
      * [Downsides](#downsides)
    * [Switching between schemes](#switching-between-schemes)
  * [Securing existing user passwords](#securing-existing-user-passwords)
    * ["Competing" solutions -> Opportunistic password rehashing only](#competing-solutions---opportunistic-password-rehashing-only)
    * [Proactive password rehashing in Fortress](#proactive-password-rehashing-in-fortress)
    * [Force resetting all passwords](#force-resetting-all-passwords)
    * [Disallowing legacy hashes](#disallowing-legacy-hashes)
    * [Informing users about forced password resets](#informing-users-about-forced-password-resets)
  * [Compatability with Core and third-party plugins](#compatability-with-core-and-third-party-plugins)
    * [TL;DR](#tldr)
    * [Detailed explanation](#detailed-explanation)
      * [Doing it wrong 01](#doing-it-wrong-01)
      * [Doing it wrong 02](#doing-it-wrong-02)
      * [Doing it wrong 03](#doing-it-wrong-03)
      * [Doing it wrong 04—Resetting the user_pass column](#doing-it-wrong-04resetting-the-userpass-column)
  * [Migrating out hashes](#migrating-out-hashes)
  * [Disabling the secure password hashing](#disabling-the-secure-password-hashing)
<!-- TOC -->

---

As of 2024, WordPress still uses
a [md5 based hashing scheme](https://github.com/WordPress/WordPress/blob/master/wp-includes/class-phpass.php#L152)
and there are [no hopes](https://core.trac.wordpress.org/ticket/39499) of that changing anytime soon.

However, fortunately, the password-hashing functions that WordPress and third-party plugins use
are [pluggable](https://codex.wordpress.org/Pluggable_Functions), which means that Fortress can replace them with
its implementations that are based on [`libsodium (ext-sodium)`](https://doc.libsodium.org/), the best
cryptography bindings available in PHP.

## Fortress password hashing

Fortress replaces
the [wp_hash_password](https://github.com/WordPress/WordPress/blob/master/wp-includes/pluggable.php#L2483)
,
[wp_check_password](https://github.com/WordPress/WordPress/blob/b879d0435401b8833bae66483895ffe189e5d35a/wp-includes/pluggable.php#L2533)
and [wp_set_password](https://github.com/WordPress/WordPress/blob/b879d0435401b8833bae66483895ffe189e5d35a/wp-includes/pluggable.php#L2705)
functions.

There are two different types of hashing schemes that Fortress can use depending on
the configuration of
the [`password.store_hashes_encrypted`](../../configuration/02_configuration_reference.md#store_hashes_encrypted)
option.

### Argon2id + Blake2b HMAC (default)

Given a user that provides the password `cocoa-hospital-wold-belt`:

1. Calculate `argon2id_hash = argon2id(cocoa-hospital-wold-belt)`
   using [`sodium_crypto_pwhash_str`](https://www.php.net/manual/de/function.sodium-crypto-pwhash-str.php).
2. Calculate `hmac = blake2b(user_id + argon2id_hash)`
   using
   keyed-Blake2B [`sodium_crypto_generichash`](https://www.php.net/manual/de/function.sodium-crypto-generichash.php).
3. Store `hmac + argon2id_hash` in the database.

#### Security benefits

- Online brute-force attacks have a near zero chance of being successful.
- Offline brute-force attacks are significantly more computation
  intensive, [argon2 is the best password hashing scheme that's currently available](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#argon2id)
  in PHP.
-

Prevents [confused deputy attacks](https://soatok.blog/2023/03/01/database-cryptography-fur-the-rest-of-us/#confused-deputies):
An attacker cannot swap his own password hash with the one of an admin user and log in using their own password.<br>
A password hash is always bound to the user ID for which it was initially created.

- An attacker with write access to the database can't insert new user accounts because they do not have the HMAC key
  which is not stored in the database.

### Argon2id + authenticated encryption

If the [`password.store_hashes_encrypted`](../../configuration/02_configuration_reference.md#store_hashes_encrypted)
option is set to `true`,
Fortress will store the argon2id hashes encrypted in the database.

(Encrypt then MAC -- xsalsa20 then keyed-Blake2b)

Given a user that provides the password `cocoa-hospital-wold-belt`:

1. Calculate `argon2id_hash = argon2id(cocoa-hospital-wold-belt)`
   using [`sodium_crypto_pwhash_str`](https://www.php.net/manual/de/function.sodium-crypto-pwhash-str.php).
2. Encrypt the argon2id hash using `xsalsa20`
   calculate `encrypted_hash = xsalsa20(argon2id_hash)`
   via [`sodium_crypto_stream_xor`](https://www.php.net/manual/en/function.sodium-crypto-stream-xor.php).
3. Authenticate the encrypted hash using `keyed-Blake2b` with the user id as additional data.
   calculate `hmac = blake2b(user_id + ciphertext)`
   via [`sodium_crypto_generichash`](https://www.php.net/manual/de/function.sodium-crypto-generichash.php) and then
   store `hmac + encrypted_hash` in the database.

#### Security benefits

This scheme provides all the benefits of the HMAC scheme above with the added benefit that:

- An attacker that can access the database can't even begin to crack the password hashes because they are
  encrypted with the [encryption key](../../getting-started/advanced-setup/secret-managment.md) which is stored outside the
  database.

#### Downsides

The encrypted scheme was the default until version `1.0.0-beta.36` of Fortress.

We chose to make it opt-in because of the reasons below:

1. Due to the added encryption,
   the final string is greater than 255 character
   which meant that we had to alter the `wp_users.user_pass` column from a `varchar(255)`
   to a `varchar(350)`.
   Fortress prevents any reset attempted/accidental reset via `dbDelta`. But some plugins might try to reset the column
   via direct database calls (`$wpdb->query('ALTER TABLE ...'`) in which case all password
   hashes will be truncated down to 255 characters which means they'd
   be [crypto-shredded](https://en.wikipedia.org/wiki/Crypto-shredding) (if you can't restore a backup).
   <br>While we think that the chance of that happening is very low & the impact is not big if you have a site with a
   handful of "staff users" that could potentially just reset their password - It's not an ideal default for hosting
   companies that deploy Fortress on a great variety of sides.
2. It's tough to migrate out of the encrypted scheme.<br>
   While it's possible to decrypt all the data, there's no way for Fortress to know what data was "hashed/encrypted"
   by `wp_hash_password` aside of passwords since plugins might use it for input that is not a user's account password.
   <br>For that reason, leaving behind a compatability layer that would allow removing Fortress while keeping the hashes
   intact would inevitably require leaving Fortress's encryption code behind and also secure manage Fortress's
   encryption secret.

We still recommend using the encrypted scheme if you (as the site owner) can live with, or handle the downsides above.

### Switching between schemes

At any time you can switch between both schemes by changing the
[`password.store_hashes_encrypted`](../../configuration/02_configuration_reference.md#store_hashes_encrypted) option.

Both schemes are compatible with each other, and a user's password will be rehashed automatically with the current
active scheme after they log in.

## Securing existing user passwords

### "Competing" solutions -> Opportunistic password rehashing only

There have been plenty [intents](https://github.com/roots/wp-password-bcrypt) of replacing the WordPress hashing with
better alternatives.

However, none of them can rehash existing legacy password hashes proactively.

Instead, typically, a user's password hash is only rehashed after successfully logging in (because this is the only time
you have access to his plaintext password).
It's an "opportunistic upgrade" strategy, which is
a [bad play](https://nakedsecurity.sophos.com/2016/12/15/yahoo-breach-ive-closed-my-account-because-it-uses-md5-to-hash-my-password/).

Assuming your site currently has `100k` users and your database is hacked two months from now.
In those two months, `20k` users logged into your site and thus had their passwords rehashed securely.

This still leaves `80K` user passwords vulnerable simply because they did not log in in on time.

### Proactive password rehashing in Fortress

In addition to opportunistic password rehashing, we managed to solve the tricky problem of rehashing the
current password hashes proactively while **still allowing users to keep their current password**.

Here is the (very) simplified summary of how this works:

1. Each password hash in the database is currently stored as `legacy_hash = legacy_hash_algo(password)`.

2. Fortress inspects `legacy_hash` and can extract the hash settings (`legacy_hash_settings`) needed to reproduce
   the same hash output for a given plaintext password (e.g., salts, costs, rounds, etc.).
   <br>`legacy_hash_settings` is then stored in the database.<br><br>Fortress can detect legacy hashes created from the
   following sources:
    - plain md5
    - phpass in compat mode
    - phpass with blowfish
    - crypt(blowfish)
    - crypt(md5)
    - crypt(sha256)
    - crypt(sha512)
    - argon2
    - argon2id
3. Fortress updates the user's password hash to `upgraded_legacy_hash = fortress_hashing(legacy_hash))`.
4. Once a user tries to log in with their (unchanged) password, Fortress checks
   if `upgraded_legacy_hash === fortress_hashing(legacy_hash_algo(password, legacy_hash_settings)))`.
5. The user is authenticated, and Fortress deletes `legacy_hash_settings` and
   stores `new_strong_hash = fortress_hashing(password))`.

You can perform this process automatically for all your users in batches **without** disrupting regular site operations using
Fortress's [`wp fort password upgrade-legacy-hashes` command](../../cli/readme.md#password-upgrade-legacy-hashes).

### Force resetting all passwords

The only downside to proactively rehashing all existing password hashes is that the plaintext passwords might be very
insecure.

> You can have the most secure password hashing in the world, but it doesn't matter if a user's password is "qwerty."

There is no way to check if stored password hashes resulted from secure passwords since we don't have access to the
plaintexts.

Fortress allows you to force-reset all or a subset of users' passwords, so they have to choose a new password that
complies with Fortress's [password policy](password-policy.md).

Surprisingly, there is no option in WordPress to bulk-reset user passwords.

For that reason, Fortress includes
the [`wp fort password force-reset-all` command](../../cli/readme.md#password-force-reset-all) to handle this
process in batches **without** disrupting normal site operation.

### Disallowing legacy hashes

Adding a malicious (undetected) admin user is one of the most common ways WordPress sites get hacked.

Whether through PHP or directly through write access at the database level, the goal is to insert
a new admin user with a known password.

For backward compatibility
reasons, [WordPress still supports plain `md5` hashes](https://github.com/WordPress/wordpress-develop/blob/6.1/src/wp-includes/pluggable.php#L2540),

So, if an attacker inserts a user with the user:

- username: `johndoe`
- password hash: `5f4dcc3b5aa765d61d8327deb882cf99` (the plain md5 of `password`)

He can log in using `johndoe/password` as his credentials.

Fortress allows you to completely prevent this from happening by setting
the [`allow_legacy_hashes`](../../configuration/02_configuration_reference.md#allow_legacy_hashes) to `false`.

From there on, only password hashes that use Fortress's secure hashing scheme will be accepted.

An attacker can never "pin" a password hash this way unless he has a full server compromise to read Fortress's
encryption keys.

**Caution!**

Only set [`allow_legacy_hashes`](../../configuration/02_configuration_reference.md#allow_legacy_hashes) to false if one
of the following conditions is met:

- You are using Fortress on a brand-new site.
- You have [upgraded all legacy hashes](#proactive-password-rehashing-in-fortress).
- You have [force reset](#force-resetting-all-passwords) all user passwords.
- As an alternative way to force every user to choose a new password.

Otherwise, you might prevent yourself and other users from logging in.

### Informing users about forced password resets

If you [force-reset all user passwords](#force-resetting-all-passwords), you need to inform your users about this to
avoid confusion.

Fortress can't know how your site handles user logins (default wp-login vs. WooCommerce vs. LMS, etc.)
which is why no visual feedback about a forced password reset can be given to users by Fortress.

If you are using the default WordPress login page, you might use the following code snippet.

```php
add_filter('login_message', function (string $message) :string{
    
    if(!empty($message)){
        return $message;
    }
    
    $url = wp_lostpassword_url();
    
    return sprintf(
        '<div id="login_error"><strong>Attention:</strong> If you created your account before the 01.01.23, you have to choose set a new password <a href="%s">here</a>.</div>',
        esc_url($url)
    );
    
});
```

---

![Force reset password notice](../../_assets/images/force-reset-password-notice.png)

---

## Compatability with Core and third-party plugins

### TL;DR

The entire Fortress password hashing functionality is 100% compatible unless a plugin does something very wrong.

### Detailed explanation

WordPress Core exposes the following password hashing APIs:

- [The PasswordHash](https://github.com/WordPress/WordPress/blob/master/wp-includes/class-phpass.php#L44) class:<br>
  Which is commonly used in Core and plugins like so:

```php
global $wp_hasher;

if ( empty( $wp_hasher ) ) {
    require_once ABSPATH . WPINC . '/class-phpass.php';
   // By default, use the portable hash from phpass.
   $wp_hasher = new PasswordHash( 8, true );
}
```

- [wp_hash_password](https://github.com/WordPress/WordPress/blob/master/wp-includes/pluggable.php#L2483):<br> Which is
  meant for low entropy input such as user-provided passwords. The default implementation of this function uses
  the [The PasswordHash](https://github.com/WordPress/WordPress/blob/master/wp-includes/class-phpass.php#L44) class.
- [wp_check_password](https://github.com/WordPress/WordPress/blob/b879d0435401b8833bae66483895ffe189e5d35a/wp-includes/pluggable.php#L2533):<br>
  Which is the counterpart to `wp_hash_password`. The default implementation of this function uses
  the [The PasswordHash](https://github.com/WordPress/WordPress/blob/master/wp-includes/class-phpass.php#L44) class.

The only scenario that might cause issues is if a plugin mixes the usage of `wp_hash/create_password`
and `PasswordHas::Hash/CreatePassword`.

Mixing the two is a **programmatic error that should be considered a bug**.

The only reason that this bug stays hidden on default WordPress installations is that the default implementations
of `wp_hash/create_password` uses `PasswordHas::Hash/CreatePassword`.

- Hashes created with `PasswordHash::HashPassword` must only be validated with `PasswordHash::CheckPassword`.
- Hashes created with `wp_hash_password` must only be validated with `wp_check_password`.

#### Doing it wrong 01

The following code will never work if Fortress is installed.

```php
$secret = 'super-secret';

$hash = wp_hash_password($secret);

$wp_hasher = new PasswordHash();

$wp_hasher->CheckPassword('super-secret', $hash);
```

However, this is a programmatic error, and not something Fortress can support.

WordPress Core does not contain this error.

#### Doing it wrong 02

The below code is also an obvious programmatic error, but it will still work with Fortress **UNLESS** you explicitly set
[allow_legacy_hashes_for_non_passwords](../../configuration/02_configuration_reference.md#allow_legacy_hashes_for_non_passwords) to `false`.

```php
$secret = 'super-secret';

$wp_hasher = new PasswordHash();

$hash = $wp_hasher->HashPassword($secret);

wp_check_password('super-secret', $hash);
```

WordPress Core does not contain this error [anymore](https://core.trac.wordpress.org/ticket/56787#ticket)
(6.2+).

#### Doing it wrong 03

A more hypothetical issue listed here for completeness is making assumptions about the output length
of `wp_hash_password`.

The hash generated by default in WordPress has a length of `34` characters.

The output of Fortress's `wp_hash_password` implementation is much larger (at least `200+` characters).

So far, we have never seen a situation where this has been an issue.

#### Doing it wrong 04—Resetting the user_pass column

This issue is listed here for completeness and is only relevant if you're
using the [encrypted password hashing scheme](#downsides).

Upon activation, Fortress will change the wp_users.user_pass column to a `varchar(350)` instead
of the `varchar(255)` that WordPress uses by default.

This is required to store the encrypted password hashes.

Fortress prevents any reset of the user_pass column to `varchar(255)` that's performed with the `dbDelta` function.

However, some plugins might try to reset/alter the `wp_users` table via direct database calls.

If the size of the `user_pass` column is reset to `varchar(255)` the currently stored data will be truncated and
hashes will be invalid and can only be restored by resetting the password or by restoring a backup.

You can verify if this happened by running the following database query:

```sql
SELECT ID, LENGTH(user_pass) AS pass_length
FROM wp_users;
```

## Migrating out hashes

If Fortress is removed WordPress will fall back to its default implementation of `wp_(hash/check)_password`.

That means that all password hashes that Fortress has created will not be valid anymore, and **all users have to reset
their password** and other data that was hashed with `wp_hash_password`.

Since hashing is a one-way function, there is no way to restore the original legacy hashes. 

We're working on a solution
that would allow you to keep supporting Fortress hashes after removal via a custom snippet / must-use plugin.

## Disabling the secure password hashing

If the possibility of having to reset users' passwords is not acceptable under any circumstance, you can disable the
secure password hashing
functionality
by setting
the [`password.include_pluggable_functions`](../../configuration/02_configuration_reference.md#include_pluggable_functions)
option to `false`.

You will still be able to use all the other functionality of the password module such as password policies,
but the default (md5-based) password
hashing scheme of WordPress will be used.

---

Next: [Password policy](password-policy.md)