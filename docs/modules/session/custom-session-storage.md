# Custom user session storage

<!-- TOC -->
* [WordPress sessions](#wordpress-sessions)
* [Fortress session storage](#fortress-session-storage)
* [Garbage collecting expired session](#garbage-collecting-expired-session)
* [Destroy all sessions](#destroy-all-sessions)
<!-- TOC -->

## WordPress sessions

**Important:** `WordPress sessions != PHP sessions ($_SESSION)`.
<br>Native PHP sessions (`ext-session`) are not used by WordPress, and this document does not refer to native sessions.

WordPress uses a custom session implementation that stores arbitrary data (the session) in the `wp_user_meta_table`.

This functionality is provided through the [`WP_Session_Tokens` class](https://developer.wordpress.org/reference/classes/wp_session_tokens/)
that Core and plugins use like so:

```php

$session_manager = WP_Session_Tokens::get_instance(get_current_user_id());

$session_data = $session_manager->get(wp_get_session_token());

$session_data['foo'] = 'bar';

$session_manager->update(wp_get_session_token(), $session_data);

```

Storing the sessions in the user_meta table has some disadvantages:

- The `wp_user_meta` table will get bloated if many users log in frequently.
- All user sessions are stored bundled together instead of separately, keyed by their token.<br>If your users use several devices, all session data for all sessions has to be retrieved for every authenticated request.
- Expired sessions can only be garbage collected once the user logs in on a different device.
- To update one session, all sessions have to be fetched first.
- (Theoretically) subject to [time-based-side-channel attacks](https://blog.ircmaxell.com/2014/11/its-all-about-time.html#Other-Types-Of-Timing-Attacks-Index-Lookup). (This will require a very skilled and highly motivated attacker)

## Fortress session storage

WordPress allows replacing the session storage through hooks, a functionality few people know. However, it's [documented and 100% compatible](https://developer.wordpress.org/reference/hooks/session_token_manager/).
Think of it as [drop-in](https://developer.wordpress.org/reference/functions/_get_dropins/).

Fortress offloads the storage of sessions to a [custom table](../../configuration/02_configuration_reference.md#table_name),
where each session represents one row with the session token being the primary key.

This crucial pre-requisite makes all the other [features](readme.md#main-features) of the session module possible.

## Garbage collecting expired session

Fortress comes with a WP-CLI command that removes expired sessions from the database.

For example, you can remove expired sessions once an hour: (This is just a starting point, you might want to change the frequency depending on the number of users on your site.)

```shell
0 * * * * wp fort session gc
```
You can find the [complete command reference here](../../cli/readme.md#session-gc).

## Destroy all sessions

Destroy all sessions is a quick and robust way to log out all users on your site and a better alternative than rotating your site's salts because (unfortunately) many plugins expect salts to stay the same.

```shell
wp fort session destroy-all
```

You can find the [complete command reference here](../../cli/readme.md#session-destroy-all).

---

Next: [Session management and security](session-managment-and-security.md).