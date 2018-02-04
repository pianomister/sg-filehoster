<?php
declare(strict_types=1);

namespace SGFilehoster;

class Utils
{
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

	public static function displayFileSize(int $size) : string
	{
		return round($size / 1024 / 1024, 0, PHP_ROUND_HALF_DOWN) . ' MB';
	}

	public static function getRandomString(int $length = 16) : string
	{
		return bin2hex(openssl_random_pseudo_bytes($length));
	}

	public static function escapeHtml($input) : string
	{
		return htmlspecialchars($input, ENT_QUOTES | ENT_HTML401, 'UTF-8');
	}

	public static function hashPassword(string $password) : string
	{
		$hash = password_hash($password . \SGFilehoster\APP_SALT, PASSWORD_DEFAULT);

		if ($hash !== null && $hash !== false) {
			return $hash;
		}

		throw \SGFilehoster\SGException('An error occured when hashing the password.');
	}

	public static function verifyPassword(string $password, string $hash) : bool
	{
		return password_verify($password . \SGFilehoster\APP_SALT, $hash);
	}
}
