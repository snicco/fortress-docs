# Logging in Fortress

<!-- TOC -->
* [Error logs](#error-logs)
* [Audit logs](#audit-logs)
<!-- TOC -->

🚨🚨 The log format and log file names are currently not covered by any backwards compatibility guarantees.
The only BC promise is that Fortress will log any type of log to the configured [log directory](../getting-started/02_installation.md#create-a-fortress-home-directory).

## Error logs

Fortress will log exceptions thrown during its execution to two places:

- To `STDERR` using the [`error_log` function](https://www.php.net/manual/en/function.error-log.php). On most WordPress websites, this will be the `wp-content/debug.log` file. Since this file might contain log messages from many sources, Fortress will prefix all its log entries with either `snicco_fortress` or `snicco_fortress`, depending on the runtime environment where the error happened.
- To its [log directory](../getting-started/02_installation.md#create-a-fortress-home-directory) using a daily log file.
  <br>If your Fortress log directory is set to `/path/to/snicco-fortress/log`, errors will be logged at:
    - `/path/to/snicco-fortress/log/2023-01-01.fortress.log`
    - `/path/to/snicco-fortress/log/2023-01-30.fortress.log`
    - etc.

Error logs are kept for three days. After that, they're deleted.

## Audit logs

Every state change that Fortress performs is logged into an audit log.
The audit log is the first place to look to troubleshoot anything related to Fortress.

Let's look at the log entries created by activating TOTP-2FA for a user.

```shell
wp fort 2fa setup admin
```

```log
[02-Feb-2023 16:30:57 UTC] 04fbb3c8f57a74c58b44aa45 {"name":"SetupCredentials","type":"command.received","data":{"user_id":1},"context":{"auth_id":0,"sapi":"cli"}}
[02-Feb-2023 16:30:57 UTC] 04fbb3c8f57a74c58b44aa45 {"name":"TOTPCredentialsWereCreated","type":"event.domain","data":{"user_id":1},"context":{"auth_id":0,"sapi":"cli"}}
[02-Feb-2023 16:30:57 UTC] 04fbb3c8f57a74c58b44aa45 {"name":"SetupCredentials","type":"command.success","data":{"user_id":1},"context":{"auth_id":0,"sapi":"cli"}}
[02-Feb-2023 16:30:57 UTC] 04fbb3c8f57a74c58b44aa45 {"name":"DestroyAllSessionsForUser","type":"command.received","data":{"user_id":1},"context":{"auth_id":0,"sapi":"cli"}}
[02-Feb-2023 16:30:57 UTC] 04fbb3c8f57a74c58b44aa45 {"name":"DestroyAllSessionsForUser","type":"command.success","data":{"user_id":1},"context":{"auth_id":0,"sapi":"cli"}}
```

Each line in the audit log consists of the following:

- A unique prefix for the current (HTTP/CLI) request.
- The name of the action that took place, i.e., : `SetupCredentials`.
- The type of action. This can be one of:
    - `command.received` => "Please do something."
    - `command.success` => "We did something successfully."
    - `command.failure` => "We could not do this."
    - `event.domain"` => "Something just happened."
- The data of the action, i.e.: `"data":{"user_id":1}` for `SetupCredentials`.
- The context in which this action happened: `{"auth_id":0,"sapi":"cli"}}`

Audit logs are logged to Fortress's [log directory](../getting-started/02_installation.md#create-a-fortress-home-directory).

If your Fortress log directory is set to `/path/to/snicco-fortress/log`, audit logs will be created at:
- `/path/to/snicco-fortress/log/2023-01-01.audit.log`
- `/path/to/snicco-fortress/log/2023-01-30.audit.log`
- etc.

Audit logs are kept for three days. After that, they're deleted.