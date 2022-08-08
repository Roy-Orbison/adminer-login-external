# External Login for Adminer

An alternative to [the insecure login plugin](https://github.com/arxeiss/Adminer-FillLoginForm) referenced on Adminer's official site.
See the [integration guide for plugins](https://www.adminer.org/en/plugins/#use) then add this plugin, as below, to grant access without revealing the authentication to the user.

```php
new AdminerLoginExternal([
	# Normal host & port, or socket.
	'server' => $server,
	# Prefill db name for convenience.
	'database' => $db,
	'username' => $db_user,
	'password' => $db_user_pw,
	# Omit for MySQL (the default), specify Adminer driver name otherwise.
	'driver' => 'pgsql',
	# Boolean of whether the current user is allowed access as the above db
	# user. Up to you how to implement that, but you should be checking this on
	# *every request* to Adminer ensure it's false when you can't verify them.
	# Don't use literal "true" unless you're using this on a private development
	# machine and PHP is only litening on a loopback address.
	'authenticated' => $logged_in,
	# Optionally override default title 'Adminer'.
	'app_name' => 'MyCo DB admin',
	# Optionally require an extra step to "log in" to Adminer. More secure and
	# maybe necessary if using other plugins that modify the login form,
	# e.g.: offer database selection.
	'manual_login' => true,
	# Optional HTML message to show when user's external authentication expired
	# whilst logged into Adminer.
	'expired_html' => <<<'EOHTML'
		<p>Your MyCo authentication expired.</p>
		EOHTML,
	# Optional HTML message to show user they are not authenticated.
	'failure_html' => <<<'EOHTML'
		<p>You need to <a href="/path/to/3p/log-in/">log in</a>.</p>
		EOHTML,
])
```
