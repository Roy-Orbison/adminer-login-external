<?php

class AdminerLoginExternal {

	protected $externals;

	/**
	 * Provide access details of your database, the current user's authentication status, and other config.
	 *
	 * @param array|object $externals An enumeration containing the fields normally completed on the login form:
	 *                                server
	 *                                database (optional, but recommended. user can always access any db the credentials allow)
	 *                                username
	 *                                password
	 *                                driver (defaults to 'server' for MySQL, but must be specified otherwise,
	 *                                        e.g. 'pgsql', 'sqlite')
	 *                                Plus fields to control the behaviour of this plugin:
	 *                                authenticated (required boolean, whether the user is known, *currently* authenticated,
	 *                                               and has privileges equivalent to the specified username)
	 *                                app_name (dynamically change the name of the tool from 'Adminer')
	 *                                manual_login (optional boolean to control auto-submission of Adminer's login form,
	 *                                              and prevents logging out whilst authenticated)
	 *                                expired_html (optional HTML message to show when user's authentication expires whilst
	 *                                              logged in to Adminer)
	 *                                failure_html (optional HTML message to show user they are not authenticated, e.g. a
	 *                                              paragraph containing a link to the login page)
	 */
	function __construct($externals) {
		$externals = (object) $externals;
		if (empty($externals->driver)) {
			$externals->driver = 'server';
		}
		if (isset($_POST['auth'])) {
			$_POST['auth']['driver'] = $externals->driver;
			$_POST['auth']['server'] = $_POST['auth']['username'] = $_POST['auth']['password'] = '';
		}
		$this->externals = $externals;
	}

	function name() {
		return empty($this->externals->app_name) ? null : $this->externals->app_name;
	}

	function credentials() {
		if (!$this->externals->authenticated) {
			# always check external stat rather than relying on adminer's session login
			auth_error(
				empty($this->externals->expired_html) ?
					'External authentication expired.' :
					$this->externals->expired_html
			);
			return false;
		}
		return [
			$this->externals->server,
			$this->externals->username,
			$this->externals->password,
		];
	}

	function database() {
		return empty($this->externals->database) ? null : $this->externals->database;
	}

	function loginForm() {
		if (!$this->externals->authenticated) {
			if (empty($this->externals->failure_html)) {
				echo '<p>You must first log in to the system that grants access to this tool.</p>';
			}
			else {
				echo $this->externals->failure_html;
			}
			return false;
		}

		if (!empty($this->externals->manual_login)) {
			return;
		}

		$nonce_attr = null;
		$response_headers = headers_list();
		while ($response_headers) {
			$response_header = array_pop($response_headers);
			if (preg_match('/^Content-Security-Policy:.*?\'nonce-([^\']+)/i', $response_header, $nonce_matches)) {
				$nonce_attr = " nonce='{$nonce_matches[1]}'";
				break;
			}
		}
		echo <<<EOHTML
<script$nonce_attr>
	document.addEventListener(
		'DOMContentLoaded',
		function() {
			document.forms[0].submit();
		},
		true
	);
</script>
EOHTML;
	}

	function loginFormField($name, $heading) {
		# only for user's benefit, submitted values are overridden by config
		$readonly = ' readonly';
		switch ($name) {
			case 'db':
				$value = isset($_GET['username']) ? (isset($_GET['db']) ? $_GET['db'] : '') : $this->database();
				$readonly = '';
				break;
			case 'driver':
				# https://github.com/vrana/adminer/pull/438
				#$value = get_driver($this->externals->driver);
				#break;
			case 'server':
			case 'username':
			case 'password':
				$name = h($name);
				return <<<EOHTML
<input type="hidden" name="auth[$name]">

EOHTML;
				break;
			default:
				return;
		}
		$value = h($value);
		return <<<EOHTML
$heading<input type="text" name="auth[$name]" value="$value"$readonly>

EOHTML;
	}

	function login($login, $password) {
		return (bool) $this->externals->authenticated;
	}
}
