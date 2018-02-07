<?php
declare(strict_types=1);

namespace SGFilehoster;

require_once __DIR__ . '/classes/exception.class.php';

// version check
if (version_compare(phpversion(), '7.0.0', '<')) {
	throw new \SGFilehoster\SGException('PHP version must be at least 7.0.0 to run this library.');
	exit();
}

// import constants
require_once __DIR__ . '/constants.php';

// import vendor packages
require_once __DIR__ . '/../vendor/autoload.php';

// setup vendor packages
define('LAZER_DATA_PATH', \SGFilehoster\DATA_PATH); //Path to folder with tables
use \Lazer\Classes\Database as DB;

// load library files
require_once __DIR__ . '/classes/utils.class.php';
require_once __DIR__ . '/classes/labels.class.php';
require_once __DIR__ . '/classes/action-handler.class.php';
require_once __DIR__ . '/classes/data-handler.class.php';
require_once __DIR__ . '/classes/view-handler.class.php';

/**
 * Public API for SG Filehoster library.
 */
class SGFilehoster
{
	/**
	 * @var bool
	 */
	private static $initialized = false;


	/**
	 * Checks if Filehoster is initialized, if not, Filehoster is initialized.
	 */
	private static function checkInitialized()
	{
		if (!self::$initialized) {
			\SGFilehoster\DataHandler::initDatabase();
			self::$initialized = true;
		}
	}


	/**
	 * Executes given action. Returns an array with data required to render the view.
	 */
	public static function executeAction(string $action, array $data) : array
	{
		self::checkInitialized();

		return \SGFilehoster\ActionHandler::execute($action, $data);
	}


	/**
	 * Returns view for page header.
	 */
	public static function getHeader() : string
	{
		return \SGFilehoster\ViewHandler::getHeader();
	}


	/**
	 * Returns view for page footer.
	 */
	public static function getFooter() : string
	{
		return \SGFilehoster\ViewHandler::getFooter();
	}


	/**
	 * Returns view for given action and provided view data.
	 */
	public static function getView(string $action = \SGFilehoster\ACTION_ERROR, array $data = []) : string
	{
		self::checkInitialized();

		return \SGFilehoster\ViewHandler::getView($action, $data);
	}
}
