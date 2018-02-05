<?php
declare(strict_types=1);

namespace SGFilehoster;

require_once 'sg-filehoster.php';
?>
<html>
	<head>
		<title>Encrypt passwords for SG Filehoster</title>
	</head>
	<body>
		<h1>Encrypt passwords for SG Filehoster</h1>
		<p>For security reasons, this generator should not be accessible for the public on your server.</p>
		<?php
		// password hash
		if (isset($_POST['pw'])) {
			$hash = \SGFilehoster\Utils::hashPassword($_POST['pw']);
			echo '<h2>Your encrypted password</h2><p>' . $hash . '</p>';
		}
		// random string
		if (isset($_POST['random'])) {
			$random = \SGFilehoster\Utils::getRandomString();
			echo '<h2>Your random string</h2><p>' . $random . '</p>';
		}
		?>
		<h2>Create random string</h2>
		<p>This tool creates a random string, which you can use to replace the APP_SALT used to secure all passwords. The salt is defined in constants.</p>
		<p>You <strong>must</strong> change the salt <strong>before</strong> generating any password hashes, because these hashes are based on the salt.</p>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<input type="submit" value="Generate random string" name="random">
		</form>
		<hr>
		<h2>Encrypt a password</h2>
		<p>This tool generates the hash for passwords which you can use within the sg-filehoster, using the salt defined in constants.</p>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<label for="pw">Your password:</label>
			<input type="password" id="pw" name="pw" placeholder="Password">
			<input type="submit" value="Encrypt password">
		</form>
	</body>
</html>