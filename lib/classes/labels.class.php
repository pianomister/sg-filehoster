<?php
declare(strict_types=1);

namespace SGFilehoster;

require_once __DIR__ . '/../labels.php';

/**
 * Labels class
 */
class Labels {
	protected static $language = null;

	/**
	 * Set language used for labels (if available); defaults to EN.
	 * Returns true if language was set, else false if language is not available in LABELS.
	 */
	public static function setLanguage(string $language) : bool
	{
		if(\SGFilehoster\LABELS && \SGFilehoster\LABELS[$language]) {
			self::$language = $language;
			return true;
		}

		return false;
	}


	public static function negotiateLanguage(array $language) : string
	{
		if (function_exists('http_negotiate_language')) {
			return http_negotiate_language(\SGFilehoster\UI_LANGUAGE);
		}

		// http_negotiate_language function not available,
		// use default (first value)
		return \SGFilehoster\UI_LANGUAGE[0];
	}


	/**
	 * Returns label with given name for current language, or '[LABEL_MISSING:$label]' if not available.
	 */
	public static function get($label)
	{
		if (!self::$language) {
			self::$language = is_array(\SGFilehoster\UI_LANGUAGE) ? self::negotiateLanguage(\SGFilehoster\UI_LANGUAGE) : \SGFilehoster\UI_LANGUAGE;
		}

		if (array_key_exists($label, \SGFilehoster\LABELS[self::$language])) {
			return \SGFilehoster\LABELS[self::$language][$label];
		}

		return '[LABEL_MISSING:' . $label . ']';
	}
}
