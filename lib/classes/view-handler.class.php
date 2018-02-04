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
	 * Renders HTML displaying errors, based on view data provided for getView().
	 * Style may be set as 'error' (default, has error layout) or 'plain' (just text with line breaks)
	 */
	private static function renderErrors(array &$data, string $style = 'error') : string
	{
		// check if errors exist in view data
		if (array_key_exists('error', $data) && count($data['error']) !== 0) {
			$template = '<p style="color: red">{errors}</p>';
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
					<a href="{file_link}">{file_name}</a>
					<a class="sg-copy-action" data-clipboard-text="{file_link}">{copy_action}</a>
				</li>
EOT;

				$names = [];
				foreach($files as $file) {
						$names[] = self::renderTemplate(
							$fileTemplate,
							[
								'file_link' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_SHORT_FILE => $file->search_id]),
								'file_name' => \SGFilehoster\Utils::escapeHtml($file->original_name),
								'copy_action' => \SGFilehoster\Labels::get('view.general.copy_action'),
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


	private static function renderDownloadLink(object $file) : string
	{
		$fileTemplate = <<<EOT
		<li>
			<a href="{file_link}" class="sg-file-list__action">
				<span>{file_name}</span>
				<span>{download_action}</span>
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
	 * Renders page header.
	 */
	public static function getHeader() : string
	{
		$template = <<<EOT
		<header>
			<a href="{title_url}" class="sg-logo">
				<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 320 320">
					<path stroke-linejoin="round" d="m135 125v-20h-55v55h55v55h-55v-20" stroke="#000000" stroke-width="20" fill="none"/>
					<path stroke-linejoin="round" d="m220 160h20v55h-55v-110h55v20" stroke="#000000" stroke-width="20" fill="none"/>
					<rect stroke-linejoin="round" height="300" width="300" stroke="#000000" y="10" x="10" stroke-width="20" fill="none"/>
				</svg>
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
					'login_url' => \SGFilehoster\Utils::getDisplayUrl([\SGFilehoster\PARAM_ACTION => \SGFilehoster\ACTION_LOGIN])
				]
			);
		}

		return self::renderTemplate(
			$template,
			[
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
		return '<footer class="sg-font-small">SG Filehoster &middot; <a href="https://github.com/pianomister">GitHub</a></footer>';
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
							<label for="files">{field_files}</label>
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
				<label for="password">{field_password}</label>
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
								<a href="{upload_link}">{all_files}</a>
								<a data-clipboard-text="{upload_link}">{copy_action}</a>
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
