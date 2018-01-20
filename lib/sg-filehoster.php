<?php
declare(strict_types=1);

namespace SGFilehoster;

// version check
if (version_compare(phpversion(), '7.2.0', '<')) {
	throw SGException('PHP version is too old to run this library.');
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
	private static function checkInitialized() : void
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

		// by default, use the same action
		$viewData = [
			'action' => $action,
			'error' => []
		];
		$viewData = $viewData + $data;

		switch($action) {

			case \SGFilehoster\ACTION_UPLOAD:
				// check if files are set
				if (!isset($_FILES['files']) || !is_uploaded_file($_FILES['files']['tmp_name'][0])) {
					$viewData['action'] = \SGFilehoster\ACTION_UPLOAD_FORM;
					$viewData['error'][] = \SGFilehoster\Labels::get('view.upload_form.error.no_file');
					break;
				}

				// if an upload password is set, validate it
				if (strlen(\SGFilehoster\UPLOAD_PW) !== 0) {
					if (!isset($_POST['password']) || !\SGFilehoster\Utils::verifyPassword($_POST['password'], \SGFilehoster\UPLOAD_PW)) {
						$viewData['action'] = \SGFilehoster\ACTION_UPLOAD_FORM;
						$viewData['error'][] = \SGFilehoster\Labels::get('view.general.error.wrong_password');
						break;
					}
				}

				// store valid uploads
				$uploads = []; 
				
				// iterate over files
				$files = &$_FILES['files'];
				for ($i = 0; $i < count($files['name']); $i++) {

					// check for file size limit
					if ($files['size'][$i] > \SGFilehoster\UPLOAD_MAX_SIZE) {
						$viewData['error'][] = self::renderTemplate(
							\SGFilehoster\Labels::get('view.upload_form.error.file_too_large'),
							[
								'file' => $files['name'][$i],
								'size' => \SGFilehoster\Utils::displayFileSize($files['size'][$i])
							]
						);
						continue;
					}

					// add id to valid files
					$uploads[] = $i;
				}

				// check if upload password is set
				$uploadPassword = '';
				if (isset($_POST['option_password'])) {
					if (isset($_POST['upload_password']) && !empty($_POST['upload_password'])) {
						// set password
						$uploadPassword = \SGFilehoster\Utils::hashPassword($_POST['upload_password']);

					} else {
						// password option was enabled, but password is empty
						$viewData['action'] = \SGFilehoster\ACTION_UPLOAD_FORM;
						$viewData['error'][] = \SGFilehoster\Labels::get('view.upload_form.error.empty_password');
					}
				}
				
				// check if upload destroy time is set
				$uploadDestroyed = -1;
				if (isset($_POST['option_time'])) {
					if (isset($_POST['upload_time'])
							&& !empty($_POST['upload_time'])
							&& isset($_POST['upload_time_unit'])
							&& !empty($_POST['upload_time_unit'])) {
						
						// calculate destroy time
						$timeStep = intval($_POST['upload_time']);
						$timeUnit = $_POST['upload_time_unit'];

						// array used to validate given unit and construct input for DateInterval
						$validUnits = [
							'months' => ['P', 'M'],
							'weeks' => ['P', 'W'],
							'days' => ['P', 'D'],
							'hours' => ['PT', 'H'],
							'minutes' => ['PT', 'M']
						];

						// check for valid values
						if ($timeStep > 0 && array_key_exists($timeUnit, $validUnits)) {
							$date = new \DateTime;
							$date->setTimestamp(time());
							$intervalString = $validUnits[$timeUnit][0] . $timeStep . $validUnits[$timeUnit][1];
							$interval = new \DateInterval($intervalString);
							$date->add($interval);
	
							$uploadDestroyed = $date->getTimeStamp();

						} else {
							// invalid data
							$viewData['action'] = \SGFilehoster\ACTION_UPLOAD_FORM;
							$viewData['error'][] = \SGFilehoster\Labels::get('view.upload_form.error.wrong_time');
						}

						
					} else {
						// destroy time option was enabled, but time is empty
						$viewData['action'] = \SGFilehoster\ACTION_UPLOAD_FORM;
						$viewData['error'][] = \SGFilehoster\Labels::get('view.upload_form.error.empty_time');
					}
				}

				// in case there is any valid file and no error so far, store the upload, otherwise exit here
				if (count($viewData['error']) !== 0 || count($uploads) === 0) {
					$viewData['action'] = \SGFilehoster\ACTION_UPLOAD_FORM;
					break;
				}

				// find unused key for new upload
				do {
					$uploadId = \SGFilehoster\Utils::getRandomString();
					$idTest = \SGFilehoster\DataHandler::uploadExists($uploadId);
				} while($idTest);

				// create new upload
				\SGFilehoster\DataHandler::createUpload([
					'search_id' => $uploadId,
					'time_destroyed' => $uploadDestroyed,
					'password' => $uploadPassword
				]);

				// set uploadId so that the view recognizes it
				$viewData['id'] = $uploadId;

				// create DB entries for each file
				for($i = 0; $i < count($uploads); $i++) {
					$f = $uploads[$i];

					if(is_uploaded_file($files['tmp_name'][$f])) {

						// find unused key for new file
						do {
							$fileId = \SGFilehoster\Utils::getRandomString();
							$idTest = \SGFilehoster\DataHandler::fileExists($fileId);
						} while($idTest);
						$fileName = $fileId . '.file';
	
						if (move_uploaded_file($files['tmp_name'][$f], \SGFilehoster\UPLOAD_PATH . $fileName)) {

							// create new file
							\SGFilehoster\DataHandler::createFile([
								'search_id' => $fileId,
								'upload_id' => $uploadId,
								'file_name' => $fileName,
								'original_name' => $files['name'][$f],
								'size' => $files['size'][$f]
							]);
						} else {
							$viewData['action'] = \SGFilehoster\ACTION_UPLOAD_FORM;
							$viewData['error'][] = \SGFilehoster\Labels::get('view.upload_form.error.save_file');
						}
					}
				}

				break;


			case \SGFilehoster\ACTION_SHOW_UPLOAD:
				// check if upload exists
				if (array_key_exists('id', $data) && \SGFilehoster\DataHandler::uploadExists($data['id'])) {

					$upload = \SGFilehoster\DataHandler::getUpload($data['id']);

					// require password by default
					$viewData['password_required'] = true;

					// check if additional password is set
					if (strlen($upload->password) !== 0) {
						
						// check validity of password
						if (isset($_POST['password'])) {
							if (\SGFilehoster\Utils::verifyPassword($_POST['password'], $upload->password)) {

								// add session access tokens for each file
								$files = \SGFilehoster\DataHandler::getFilesForUpload($upload->search_id);
								if ($files !== null) {
									session_start();

									foreach ($files as $file) {
										$_SESSION['token_' . $file->search_id] = $file->token;
									}
								}

								$viewData['password_required'] = false;
								break;
							} else {
								$viewData['error'][] = Labels::get('view.general.error.wrong_password');
								break;
							}
						}
					} else {
						// add session access tokens for each file
						$files = \SGFilehoster\DataHandler::getFilesForUpload($upload->search_id);
						if ($files !== null) {
							session_start();

							foreach ($files as $file) {
								$_SESSION['token_' . $file->search_id] = $file->token;
							}
						}

						$viewData['password_required'] = false;
					}
					break;
				}

				// in case anything goes wrong, show as 'not found'
				$viewData['action'] = \SGFilehoster\ACTION_NOT_FOUND;
				$viewData['error'][] = \SGFilehoster\Labels::get('view.show_upload.error.not_found');

				break;


			case \SGFilehoster\ACTION_SHOW_FILE:
				// check if file exists
				if (array_key_exists('id', $data) && \SGFilehoster\DataHandler::fileExists($data['id'])) {
					$file = \SGFilehoster\DataHandler::getFileWithUpload($data['id']);
					$upload = $file->{\SGFilehoster\TABLE_UPLOAD};

					// require password by default
					$viewData['password_required'] = true;

					// check if additional password is set
					if (strlen($upload->password) !== 0) {

						// check validity of password
						if (isset($_POST['password'])) {
							if (\SGFilehoster\Utils::verifyPassword($_POST['password'], $upload->password)) {

								// add session access token for file
								session_start();
								$_SESSION['token_' . $file->search_id] = $file->token;

								$viewData['password_required'] = false;
								break;

							} else {
								$viewData['error'][] = Labels::get('view.general.error.wrong_password');
								break;
							}
						}

					} else {
						// add session access token for file
						session_start();
						$_SESSION['token_' . $file->search_id] = $file->token;

						$viewData['password_required'] = false;
					}

					break;
				}

				// in case anything goes wrong, show as 'not found'
				$viewData['action'] = \SGFilehoster\ACTION_NOT_FOUND;
				$viewData['error'][] = \SGFilehoster\Labels::get('view.show_file.error.not_found');

				break;


			case \SGFilehoster\ACTION_DOWNLOAD:
				// check if file exists
				if (array_key_exists('id', $data) && \SGFilehoster\DataHandler::fileExists($data['id'])) {

					$file = \SGFilehoster\DataHandler::getFile($data['id']);

					session_start();

					// check validity of token
					if (isset($_SESSION['token_' . $file->search_id]) && hash_equals($file->token, $_SESSION['token_' . $file->search_id])) {

						// download file directly
						\SGFilehoster\DataHandler::serveFile($data['id']);
						break;
					}
				}
				$viewData['action'] = \SGFilehoster\ACTION_NOT_FOUND;
				$viewData['error'][] = \SGFilehoster\Labels::get('view.show_file.error.not_found');

				break;
		}

		return $viewData;
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
