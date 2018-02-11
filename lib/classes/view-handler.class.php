<?php
declare(strict_types=1);

namespace SGFilehoster;

use \StringTemplate\Engine;

class ViewHandler
{
	/**
	 * @var \StringTemplate\Engine
	 */
	private static $template = null;


	/**
	 * Renders given template with provided replace values.
	 */
	private static function renderTemplate(string $template, array $replace) : string
	{
		if (self::$template === null) {
			// initialize template engine instance
			self::$template = new Engine;
		}

		return self::$template->render($template, $replace);
	}


	/**
	 * Checks MIME type for given file, and determines best-fit icon
	 * to be displayed for that file.
	 */
	private static function getFileIcon($file) : string
	{
		$path = realpath(\SGFilehoster\UPLOAD_PATH . $file->file_name);
		$mime = mime_content_type($path);

		// text document
		if (preg_match('/^application\/pdf/', $mime) === 1
				|| preg_match('/^text/', $mime) === 1) {
			return 'file_text_data';
		// image
		} elseif (preg_match('/^image/', $mime) === 1) {
			return 'file_image';
		// audio
		} elseif (preg_match('/^audio/', $mime) === 1) {
			return 'file_music_player';
		// video
		} elseif (preg_match('/^video/', $mime) === 1) {
			return 'file_player_media';
		}

		// default: simple file icon
		return 'file';
	}


	/**
	 * Renders HTML displaying errors, based on view data provided for getView().
	 * Style may be set as 'error' (default, has error layout) or 'plain' (just text with line breaks)
	 */
	private static function renderErrors(array &$data, string $style = 'error') : string
	{
		// check if errors exist in view data
		if (array_key_exists('error', $data) && count($data['error']) !== 0) {
			$template = '<p class="sg-message sg-message--error">{errors}</p>';
			if ($style === 'plain') {
				$template = '{errors}';
			}
			return self::renderTemplate(
				$template,
				[
					'errors' => implode('<br>', $data['error'])
				]
			);
		}

		// no errors
		return '';
	}


	/**
	 * Renders HTML to show a table with file sharing links, based on view data provided for getView().
	 */
	private static function renderUploadTable(array &$data) : string
	{
		// check for id
		if (array_key_exists('id', $data)) {

			// get data for upload
			$files = \SGFilehoster\DataHandler::getFilesForUpload($data['id']);

			if ($files !== null) {

				$fileTemplate = <<<EOT
				<li>
					<a href="{file_link}" class="sg-text-icon"><i class="sg-icon-{file_icon}"></i>{file_name}</a>
					<span class="sg-link sg-copy-action sg-action-icon"
								data-copy-success="{copy_success}"
								data-copy-error="{copy_error}"
								data-clipboard-text="{file_link}"
								title="{copy_action}"><i class="sg-icon-fileboard_plus"></i></span>
				</li>
EOT;

				$names = [];
				foreach($files as $file) {
						$names[] = self::renderTemplate(
							$fileTemplate,
							[
								'file_link' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_SHORT_FILE => $file->search_id]),
								'file_icon' => self::getFileIcon($file),
								'file_name' => \SGFilehoster\Utils::escapeHtml($file->original_name),
								'copy_action' => \SGFilehoster\Labels::get('view.general.copy_action'),
								'copy_success' => \SGFilehoster\Labels::get('view.general.copy_success'),
								'copy_error' => \SGFilehoster\Labels::get('view.general.copy_error')
							]
						);
				}

				return self::renderTemplate(
					'<ul class="sg-file-list sg-file-list--interactive">{files}</ul>',
					[
						'files' => implode('', $names)
					]
				);
			}
		}

		// no files
		return '';
	}


	/**
	 * Renders HTML to show admin table with file sharing and delete links,
	 * based on given upload.
	 */
	private static function renderAdminTable($upload) : string
	{
		$template = <<<EOT
		<h2 class="sg-align-space-between">
			{date}
			<a class="sg-font-copy sg-text-icon" href="{delete_link}"><i class="sg-icon-delete"></i>{delete_label}</a>
		</h2>
		<p class="sg-font-small">{protection}</p>
		<ul class="sg-file-list sg-file-list--interactive">
			<li>
				<a class="sg-text-icon" href="{upload_link}"><i class="sg-icon-folder"></i>{all_files}</a>
				<span>
					<span class="sg-link sg-copy-action sg-action-icon"
								data-copy-success="{copy_success}"
								data-copy-error="{copy_error}"
								data-clipboard-text="{upload_link}"
								title="{copy_action}"><i class="sg-icon-fileboard_plus"></i></span>
					<a href="{delete_link}"
						 title="{delete_label}"
						 class="sg-action-icon"><i class="sg-icon-delete"></i></a>
				</span>
			</li>
		{files}
		</ul>
EOT;

		// get protection methods for upload
		$protection = [];
		if ($upload->password !== '') {
			$protection[] = self::renderTemplate(
				'<span class="sg-text-icon sg-tooltipped sg-tooltipped-s" aria-label="{password_visibility}"><i class="sg-icon-lock_close_round"></i>{password_protection}</span>',
				[
					'password_visibility' => \SGFilehoster\Labels::get('view.admin.password_visibility'),
					'password_protection' => \SGFilehoster\Labels::get('view.admin.password_protection'),
				]
			);
		}
		if ($upload->time_destroyed !== -1) {
			$protection[] = self::renderTemplate(
				'<span class="sg-text-icon"><i class="sg-icon-clock"></i>' . \SGFilehoster\Labels::get('view.admin.time_destroyed') . '</span>',
				[
					'time' => date('d.m.Y H:i', $upload->time_destroyed)
				]
			);
		}

		// render file list
		$files = $upload->{\SGFilehoster\TABLE_FILE}->findAll();
		$fileTemplate = <<<EOT
		<li>
			<a href="{file_link}" class="sg-text-icon"><i class="sg-icon-{file_icon}"></i>{file_name}</a>
			<span>
				<span class="sg-link sg-copy-action sg-action-icon"
							data-copy-success="{copy_success}"
							data-copy-error="{copy_error}"
							data-clipboard-text="{file_link}"
							title="{copy_action}"><i class="sg-icon-fileboard_plus"></i></span>
				<a href="{delete_file_link}"
					 title="{delete_file_label}"
					 class="sg-action-icon"><i class="sg-icon-delete"></i></a>
			</span>
		</li>
EOT;

		$names = [];
		foreach($files as $file) {
			$names[] = self::renderTemplate(
				$fileTemplate,
				[
					'file_link' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_SHORT_FILE => $file->search_id]),
					'file_icon' => self::getFileIcon($file),
					'file_name' => \SGFilehoster\Utils::escapeHtml($file->original_name),
					'copy_action' => \SGFilehoster\Labels::get('view.general.copy_action'),
					'copy_success' => \SGFilehoster\Labels::get('view.general.copy_success'),
					'copy_error' => \SGFilehoster\Labels::get('view.general.copy_error'),
					'delete_file_link' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_ACTION => \SGFilehoster\ACTION_ADMIN, \SGFilehoster\PARAM_SHORT_FILE => $file->search_id]),
					'delete_file_label' => \SGFilehoster\Labels::get('view.general.delete_action')
				]
			);
		}

		// render upload admin table
		return self::renderTemplate(
			$template,
			[
				'date' => date('d.m.Y H:i', $upload->time_created),
				'delete_link' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_ACTION => \SGFilehoster\ACTION_ADMIN, \SGFilehoster\PARAM_SHORT_UPLOAD => $upload->search_id]),
				'delete_label' => \SGFilehoster\Labels::get('view.general.delete_action'),
				'protection' => count($protection) !== 0 ? implode('<br>', $protection) : '<span class="sg-text-icon"><i class="sg-icon-lock_open_round"></i>' . \SGFilehoster\Labels::get('view.admin.no_protection') . '</span>',
				'all_files' => \SGFilehoster\Labels::get('view.upload.all_files'),
				'upload_link' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_SHORT_UPLOAD => $upload->search_id]),
				'copy_action' => \SGFilehoster\Labels::get('view.general.copy_action'),
				'copy_success' => \SGFilehoster\Labels::get('view.general.copy_success'),
				'copy_error' => \SGFilehoster\Labels::get('view.general.copy_error'),
				'files' => implode('', $names)
			]
		);
	}


	/**
	 * Renders HTML for single download link entry in file list.
	 */
	private static function renderDownloadLink($file) : string
	{
		$fileTemplate = <<<EOT
		<li>
			<a href="{file_link}" class="sg-file-list__action">
				<span class="sg-text-icon"><i class="sg-icon-{file_icon}"></i>{file_name}</span>
				<span class="sg-text-icon"><i class="sg-icon-cloud_down"></i>{download_action}</span>
			</a>
		</li>
EOT;

		return self::renderTemplate(
					$fileTemplate,
					[
						'file_link' => \SGFilehoster\Utils::getDisplayUrl([
							\SGFilehoster\PARAM_ACTION => \SGFilehoster\ACTION_DOWNLOAD,
							\SGFilehoster\PARAM_SHORT_FILE => $file->search_id
						]),
						'file_icon' => self::getFileIcon($file),
						'file_name' => \SGFilehoster\Utils::escapeHtml($file->original_name),
						'download_action' => \SGFilehoster\Labels::get('view.download.button_download')
					]
				);
	}


	/**
	 * Renders HTML to show download link for a single file.
	 */
	private static function renderDownloadFile(array &$data) : string
	{
		// check for id
		if (array_key_exists('id', $data)) {

			// get data for file
			$file = \SGFilehoster\DataHandler::getFile($data['id']);

			if ($file !== null) {
				return '<ul class="sg-file-list sg-file-list--interactive">' . self::renderDownloadLink($file) . '</ul>';
			}
		}

		// no file found
		return '';
	}


	/**
	 * Renders HTML to show a table with file download links, based on view data provided for getView().
	 */
	private static function renderDownloadTable(array &$data) : string
	{
		// check for id
		if (array_key_exists('id', $data)) {

			// get data for upload
			$files = \SGFilehoster\DataHandler::getFilesForUpload($data['id']);

			if ($files !== null) {

				$names = [];
				foreach($files as $file) {
						$names[] = self::renderDownloadLink($file);
				}

				return self::renderTemplate(
					'<ul class="sg-file-list sg-file-list--interactive">{files}</ul>',
					[
						'files' => implode('', $names)
					]
				);
			}
		}

		// no files
		return '';
	}


	/**
	 * Renders a password input form, with action pointing to provided param with id from data.
	 */
	private static function renderPasswordForm(array &$data, string $param) : string
	{
		$template = <<<EOT
		<p>{body}</p>
		<form action="{action}" method="post">
			<input type="password" name="password" />
			<input type="submit" value="{button_login}" />
		</form>
EOT;

		return self::renderTemplate(
			$template,
			[
				'body' => \SGFilehoster\Labels::get('view.general.password_required'),
				'action' => \SGFilehoster\Utils::getDisplayUrl([$param => $data['id']]),
				'button_login' => \SGFilehoster\Labels::get('view.general.button_login')
			]
		);
	}


	/**
	 * Renders login form for administration area.
	 */
	private static function renderLoginForm() : string
	{
		$template = <<<EOT
		<article>
			<form action="{action}" method="post">
				<p>
					<label for="username" class="sg-text-icon"><i class="sg-icon-profile"></i>{field_username}</label>
					<input name="username" type="text" id="username" placeholder="{field_username}" />
				</p>
				<p>
					<label for="password" class="sg-text-icon"><i class="sg-icon-lock_close_round"></i>{field_password}</label>
					<input name="password" type="password" id="password" placeholder="{field_password}" />
				</p>
				<div class="sg-button-group">
					<input type="submit" value="{button_login}">
				</div>
			</form>
		</article>
EOT;

		return self::renderTemplate(
			$template,
			[
				'action' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_ACTION => \SGFilehoster\ACTION_ADMIN]),
				'field_username' => \SGFilehoster\Labels::get('view.login.field_username'),
				'field_password' => \SGFilehoster\Labels::get('view.login.field_password'),
				'button_login' => \SGFilehoster\Labels::get('view.general.button_login')
			]
		);
	}


	/**
	 * Renders administration area.
	 */
	private static function renderAdmin() : string
	{
		$content = '<p>' . \SGFilehoster\Labels::get('view.admin.body') . '</p>';

		$template = <<<EOT
		<section>
			{upload}
		</section>
EOT;

		$uploads = \SGFilehoster\DataHandler::getAllUploadsWithFiles();
		foreach($uploads as $upload) {
			$content .= self::renderTemplate(
				$template,
				[
					'upload' => self::renderAdminTable($upload)
				]
			);
		}

		return $content;
	}


	/**
	 * Renders page header.
	 */
	public static function getHeader() : string
	{
		$template = <<<EOT
		<header>
			<a href="{title_url}" class="sg-logo">
				{logo}
				{title}
			</a>
			{login_link}
		</header>
EOT;

		$linkTemplate = '<nav><ul><li><a href="{login_url}">{login}</a></li></ul></nav>';

		$linkRendered = '';
		if (\SGFilehoster\UI_SHOW_ADMIN) {
			$linkRendered = self::renderTemplate(
				$linkTemplate,
				[
					'login' => \SGFilehoster\Labels::get('view.general.login'),
					'login_url' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_ACTION => \SGFilehoster\ACTION_ADMIN])
				]
			);
		}

		return self::renderTemplate(
			$template,
			[
				'logo' => \SGFilehoster\UI_LOGO,
				'title' => \SGFilehoster\UI_TITLE,
				'title_url' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_ACTION => \SGFilehoster\UI_HOMEPAGE]),
				'login_link' => $linkRendered
			]
		);
	}


	/**
	 * Renders page footer.
	 */
	public static function getFooter() : string
	{
		return '<footer class="sg-font-small">SG Filehoster &middot; <a href="https://github.com/pianomister/sg-filehoster">GitHub</a></footer>';
	}


	/**
	 * Renders view for given action and data.
	 */
	public static function getView(string $action, array $data = []) : string
	{
		switch ($action) {

			case \SGFilehoster\ACTION_WELCOME:
				$welcome = self::renderTemplate(
					'<main><h1>{title}</h1><p>{body}</p></main>',
					[
						'title' => \SGFilehoster\Labels::get('view.welcome.title'),
						'body' => \SGFilehoster\Labels::get('view.welcome.body')
					]
				);

				if (\SGFilehoster\UI_WELCOME_SHOW_UPLOAD_FORM) {
					$welcome .= self::getView(\SGFilehoster\ACTION_UPLOAD_FORM);
				}

				return $welcome;


			case \SGFilehoster\ACTION_UPLOAD_FORM:
				$template = <<<EOT
				<main><article>
					<h2>{title}</h2>
					{errors}
					<form action="{action}" enctype="multipart/form-data" method="post">
						<p class="sg-input-file">
							<input name="files[]" id="files" type="file" multiple
										 data-multiple-caption="{files_selected}" />
							<label for="files"><i class="sg-icon-cloud_up"></i><span>{field_files}</span></label>
							<small>{size_limit} {size_limit_value}</small>
						</p>
						<p>
							<input type="checkbox" class="sg-control" id="option_password" name="option_password" />
							<label for="option_password">
								{field_option_password}
							</label>
							<span class="sg-control-checked-visible">
								<label for="upload_password">{field_upload_password}</label>
								<input type="password" id="upload_password" name="upload_password" />
							</span>
						</p>

						<p>
							<input type="checkbox" class="sg-control" id="option_time" name="option_time" />
							<label for="option_time">
								{field_option_time}
							</label>
							<span class="sg-control-checked-visible">
								<label for="upload_time">{field_upload_time}</label>
								<input type="number" id="upload_time" name="upload_time"
											min="1" max="99" step="1"
											size="2" maxlength="2"
											oninput="validity.valid||(value='');" />
								<span class="sg-select">
									<select name="upload_time_unit">
										<option value="minutes">{value_upload_time_minutes}</option>
										<option value="hours">{value_upload_time_hours}</option>
										<option value="days">{value_upload_time_days}</option>
										<option value="weeks">{value_upload_time_weeks}</option>
										<option value="months">{value_upload_time_months}</option>
									</select>
									<span class="sg-select__arrow"></span>
								</span>
							</span>
						</p>

						{password}
						<div class="sg-button-group">
							<input type="submit" value="{button_submit}" />
						</div>
					</form>
				</article></main>
EOT;

				$passwordTemplate = <<<EOT
				<label for="password" class="sg-text-icon"><i class="sg-icon-lock_close_round"></i>{field_password}</label>
				<input name="password" type="password" id="password" />
EOT;

				$passwordRendered = '';
				if (strlen(\SGFilehoster\UPLOAD_PW) !== 0) {
					$passwordRendered = self::renderTemplate(
						$passwordTemplate,
						[
							'field_password' => \SGFilehoster\Labels::get('view.upload_form.field_password'),
						]
					);
				}

				return self::renderTemplate(
					$template,
					[
						'title' => \SGFilehoster\Labels::get('view.upload_form.title'),
						'size_limit' => \SGFilehoster\Labels::get('view.upload_form.size_limit'),
						'size_limit_value' => \SGFilehoster\Utils::displayFileSize(\SGFilehoster\UPLOAD_MAX_SIZE),
						'errors' => self::renderErrors($data),
						'action' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_ACTION => \SGFilehoster\ACTION_UPLOAD]),
						'field_files' => \SGFilehoster\Labels::get('view.upload_form.field_files'),
						'drop_area' => \SGFilehoster\Labels::get('view.upload_form.drop_area'),
						'files_selected' => \SGFilehoster\Labels::get('view.upload_form.files_selected'),
						'field_option_password' => \SGFilehoster\Labels::get('view.upload_form.field_option_password'),
						'field_upload_password' => \SGFilehoster\Labels::get('view.upload_form.field_upload_password'),
						'field_option_time' => \SGFilehoster\Labels::get('view.upload_form.field_option_time'),
						'field_upload_time' => \SGFilehoster\Labels::get('view.upload_form.field_upload_time'),
						'value_upload_time_minutes' => \SGFilehoster\Labels::get('view.upload_form.value_upload_time_minutes'),
						'value_upload_time_hours' => \SGFilehoster\Labels::get('view.upload_form.value_upload_time_hours'),
						'value_upload_time_days' => \SGFilehoster\Labels::get('view.upload_form.value_upload_time_days'),
						'value_upload_time_weeks' => \SGFilehoster\Labels::get('view.upload_form.value_upload_time_weeks'),
						'value_upload_time_months' => \SGFilehoster\Labels::get('view.upload_form.value_upload_time_months'),
						'password' => $passwordRendered,
						'button_submit' => \SGFilehoster\Labels::get('view.upload_form.button_submit'),
					]
				);
				break;


			case \SGFilehoster\ACTION_UPLOAD:
				$template = <<<EOT
				<main>
					<article>
						<h2>{title}</h2>
						{errors}
						<h3 class="sg-h4">{share_all_files}</h3>
						<ul class="sg-file-list sg-file-list--interactive">
							<li>
								<a href="{upload_link}" class="sg-text-icon"><i class="sg-icon-folder"></i>{all_files}</a>
								<span class="sg-link sg-copy-action sg-action-icon"
											data-copy-success="{copy_success}"
											data-copy-error="{copy_error}"
											data-clipboard-text="{upload_link}"
											title="{copy_action}"><i class="sg-icon-fileboard_plus"></i></span>
							</li>
						</ul>
						<h3 class="sg-h4">{share_individual_files}</h3>
						{table}
					</article>
				</main>
EOT;

				return self::renderTemplate(
					$template,
					[
						'title' => \SGFilehoster\Labels::get('view.upload.title'),
						'share_all_files' => \SGFilehoster\Labels::get('view.upload.share_all_files'),
						'all_files' => \SGFilehoster\Labels::get('view.upload.all_files'),
						'upload_link' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_SHORT_UPLOAD => $data['id']]),
						'copy_action' => \SGFilehoster\Labels::get('view.general.copy_action'),
						'copy_success' => \SGFilehoster\Labels::get('view.general.copy_success'),
						'copy_error' => \SGFilehoster\Labels::get('view.general.copy_error'),
						'errors' => self::renderErrors($data),
						'share_individual_files' => \SGFilehoster\Labels::get('view.upload.share_individual_files'),
						'table' => self::renderUploadTable($data)
					]
				);
				break;


			case \SGFilehoster\ACTION_SHOW_UPLOAD:
				if (!isset($data['password_required'])) {
					$data['password_required'] = true;
				}

				$template = <<<EOT
				<main>
					<article>
						<h2>{title}</h2>
						{errors}
						{table}
					</article>
				</main>
EOT;

				return self::renderTemplate(
					$template,
					[
						'title' => \SGFilehoster\Labels::get('view.show_upload.title'),
						'errors' => self::renderErrors($data),
						'table' => $data['password_required'] ? self::renderPasswordForm($data, \SGFilehoster\PARAM_SHORT_UPLOAD) : self::renderDownloadTable($data)
					]
				);
				break;


			case \SGFilehoster\ACTION_SHOW_FILE:
				if (!isset($data['password_required'])) {
					$data['password_required'] = true;
				}

				$template = <<<EOT
				<main>
					<article>
						<h2>{title}</h2>
						{errors}
						{file}
					</article>
				</main>
EOT;

				return self::renderTemplate(
					$template,
					[
						'title' => \SGFilehoster\Labels::get('view.show_file.title'),
						'errors' => self::renderErrors($data),
						'file' => $data['password_required'] ? self::renderPasswordForm($data, \SGFilehoster\PARAM_SHORT_FILE) : self::renderDownloadFile($data)
					]
				);
				break;


			case \SGFilehoster\ACTION_ADMIN:
				if (!isset($data['password_required'])) {
					$data['password_required'] = true;
				}

				$template = <<<EOT
				<main>
					<div class="sg-align-space-between">
						<h1>{title}</h1>
						{logout_button}
					</div>
					{errors}
					{content}
				</main>
EOT;

				return self::renderTemplate(
					$template,
					[
						'title' => \SGFilehoster\Labels::get('view.admin.title'),
						'logout_button' => $data['password_required'] ? '' : '<a href="' . \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_ACTION => \SGFilehoster\ACTION_LOGOUT]) . '">' . \SGFilehoster\Labels::get('view.general.button_logout') . '</a>',
						'errors' => self::renderErrors($data),
						'content' => $data['password_required'] ? self::renderLoginForm() : self::renderAdmin($data)
					]
				);
				break;


			case \SGFilehoster\ACTION_NOT_FOUND:
				$template = <<<EOT
				<main>
					<article>
						<h2>{title}</h2>
						<p>{body}</p>
					</article>
				</main>
EOT;

				$messages = self::renderErrors($data, 'plain');

				return self::renderTemplate(
					$template,
					[
						'title' => \SGFilehoster\Labels::get('view.not_found.title'),
						'body' => (strlen($messages) > 0) ? $messages : \SGFilehoster\Labels::get('view.not_found.body'),
					]
				);
				break;


			case \SGFilehoster\ACTION_ERROR:
				$messages = self::renderErrors($data);

				return self::renderTemplate(
					'<main><article><h2>{title}</h2>{body}</article></main>',
					[
						'title' => \SGFilehoster\Labels::get('view.error.title'),
						'body' => (strlen($messages) > 0) ? $messages : '<p>' . \SGFilehoster\Labels::get('view.error.body') . '</p>'
					]
				);
		}

		// return default, if no action was applicable
		return self::getView(\SGFilehoster\UI_HOMEPAGE);
	}
}
