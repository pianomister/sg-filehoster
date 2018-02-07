<?php
declare(strict_types=1);

namespace SGFilehoster;

class Utils
{
	/**
	 * Generates an URL used for links in the application,
	 * with optional parameters given as associative array
	 * (query string param => value)
	 */
	public static function getDisplayUrl(array $queryParams = []) : string
	{
		$url = \SGFilehoster\UI_DISPLAY_URL;

		if (count($queryParams) !== 0) {
			$url .= '?';
			$queryString = [];
			foreach ($queryParams as $param => $value) {
				$queryString[] = $param . '=' . $value;
			}
			$url .= implode('&', $queryString);
		}

		return $url;
	}


	/**
	 * Formats $size in bytes as MB, to be printed on page.
	 */
	public static function displayFileSize(int $size) : string
	{
		return round($size / 1024 / 1024, 0, PHP_ROUND_HALF_DOWN) . ' MB';
	}


	/**
	 * Returns a random string with given length, by default created based on 16 bytes.
	 */
	public static function getRandomString(int $length = 16) : string
	{
		return bin2hex(openssl_random_pseudo_bytes($length));
	}


	/**
	 * Escapes HTML for output on web page, used to ensure that
	 * user input does not harm the application.
	 */
	public static function escapeHtml($input) : string
	{
		return htmlspecialchars($input, ENT_QUOTES | ENT_HTML401, 'UTF-8');
	}


	/**
	 * Creates a hash using the default hash algorithm and a salt
	 * defined in the APP_SALT constant.
	 */
	public static function hashPassword(string $password) : string
	{
		$hash = password_hash($password . \SGFilehoster\APP_SALT, PASSWORD_DEFAULT);

		if ($hash !== null && $hash !== false) {
			return $hash;
		}

		throw new \SGFilehoster\SGException('An error occured when hashing the password.');
	}


	/**
	 * Verifies a given password compared to a given hash, also using the
	 * salt defined in APP_SALT constant. This verifies passwords created with
	 * hashPassword() method.
	 */
	public static function verifyPassword(string $password, string $hash) : bool
	{
		return password_verify($password . \SGFilehoster\APP_SALT, $hash);
	}
}
