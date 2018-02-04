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
		<?php
		if (isset($_POST['pw'])) {
			$hash = \SGFilehoster\Utils::hashPassword($_POST['pw']);
			echo '<h2>Your encrypted password</h2><p>' . $hash . '</p>';
		}
		?>
		<h2>Encrypt a password</h2>
		<p>This tool generates the hash for passwords which you can use within the sg-filehoster, using the salt defined in constants.</p>
		<p>For security reasons, this generator should not be accessible for the public on your server.</p>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<label for="pw">Your password:</label>
			<input type="password" id="pw" name="pw" placeholder="Password">
			<input type="submit" value="Encrypt password">
		</form>
	</body>
</html>