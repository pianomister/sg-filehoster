<?php
declare(strict_types=1);

namespace SGFilehoster;

const APP_SALT = '6ct6fjvpe95oc532sgv6gddmaa04qqbd2z2wbdpz'; // salt added to all passwords, should be changed to any random value for each installation!

// admin credentials
const ADMIN_USER = 'admin'; // username to access admin panel
const ADMIN_PW = '$2y$10$MbJ20a2MQkYKPzhJxscQIuzuv2U6NgsyNcPe..QnbujrZ6d0fk9kS'; // password to access admin panel. Must be generated using pwgenerator.php script.

// URL param names
const PARAM_ACTION = 'action';
const PARAM_SHORT_UPLOAD = 'u';
const PARAM_SHORT_FILE = 'f';

// strings used as values for 'action' URL param
const ACTION_ADMIN = 'admin';
const ACTION_LOGOUT = 'logout';
const ACTION_WELCOME = 'welcome'; // welcome page / home page
const ACTION_UPLOAD_FORM = 'upload_form'; // show upload form in UI
const ACTION_UPLOAD = 'upload'; // upload action, called by upload form
const ACTION_DOWNLOAD = 'download'; // download action, will serve the file
const ACTION_SHOW_UPLOAD = 'show_upload'; // show upload in UI
const ACTION_SHOW_FILE = 'show_file'; // show file in UI
const ACTION_NOT_FOUND = 'not_found'; // show 'not found' message in UI
const ACTION_ERROR = 'error'; // show 'error' message in UI

// UI configuration
const UI_TITLE = 'SG Filehoster'; // app title shown in UI
define(__NAMESPACE__ . '\UI_DISPLAY_URL', '//' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']); // URL used to build share links for uploads and files. Must point to index.php file
const UI_LOGO = ''; // optional, includes custom logo in UI. Must be an svg or img tag (HTML)
const UI_SHOW_ADMIN = true; // shows link to admin login page on each page
const UI_LANGUAGE = ['en', 'de']; // default language for labels in UI. Either a string (e.g. 'en') to set language, or array (e.g. ['en', 'de']) to determine best fit.
const UI_HOMEPAGE = ACTION_WELCOME; // default action executed if filehoster index page is called. Should be an action that has a UI view.
const UI_WELCOME_SHOW_UPLOAD_FORM = true; // true if upload form should be included within the welcome page

define(__NAMESPACE__ . '\DATA_PATH', __DIR__ . '/../data/'); // directory where Lazer config files are stored, must end with a slash
const UPLOAD_PATH = DATA_PATH . 'uploads/'; // directory where uploads are stored, must end with a slash
const UPLOAD_PW = ''; // set a password to protect the upload form. Must be generated using pwgenerator.php script.
const UPLOAD_MAX_SIZE = 20971520; // max size of a single uploaded file, default 20971520 = 20 MB


// database table names and fields
const TABLE_UPLOAD = 'SGUpload';
const TABLE_UPLOAD_STRUCTURE = array(
	'search_id' => 'string',
	'time_created' => 'integer',
	'time_destroyed' => 'integer',
	'password' => 'string'
);
const TABLE_FILE = 'SGFile';
const TABLE_FILE_STRUCTURE = array(
	'search_id' => 'string',
	'upload_id' => 'string',
	'file_name' => 'string',
	'original_name' => 'string',
	'size' => 'integer',
	'token' => 'string'
);
