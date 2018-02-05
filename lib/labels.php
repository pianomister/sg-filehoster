<?php
declare(strict_types=1);

namespace SGFilehoster;

const LABELS = [
	'EN' => [
		'view.general.login' => 'Log in',
		'view.general.button_login' => 'Sign in',
		'view.general.password_required' => 'This access is protected. Please enter the password.',
		'view.general.error.wrong_password' => 'The password you entered is not correct.',
		'view.general.copy_action' => 'Copy link',

		'view.welcome.title' => 'Simply share files.',
		'view.welcome.body' => 'Upload one or more files, protect by password or time-boxed access, share the download link.',

		'view.upload_form.title' => 'Upload',
		'view.upload_form.size_limit' => 'File size limit:',
		'view.upload_form.field_files' => 'Select files ...',
		'view.upload_form.files_selected' => '{count} files selected',
		'view.upload_form.field_option_password' => 'Protect access to uploaded files',
		'view.upload_form.field_upload_password' => 'with password',
		'view.upload_form.field_option_time' => 'Files are available for limited time',
		'view.upload_form.field_upload_time' => 'and deleted after',
		'view.upload_form.value_upload_time_minutes' => 'Minute(s)',
		'view.upload_form.value_upload_time_hours' => 'Hour(s)',
		'view.upload_form.value_upload_time_days' => 'Day(s)',
		'view.upload_form.value_upload_time_weeks' => 'Week(s)',
		'view.upload_form.value_upload_time_months' => 'Month(s)',
		'view.upload_form.field_password' => 'Upload password',
		'view.upload_form.button_submit' => 'Upload',
		'view.upload_form.error.no_file' => 'Please select at least one file to upload.',
		'view.upload_form.error.file_too_large' => 'The file \'{file}\' is too large ({size}).',
		'view.upload_form.error.empty_password' => 'If you would like to protect access to your upload, please provide a password.',
		'view.upload_form.error.empty_time' => 'The option for destroy time was selected, but no time was provided.',
		'view.upload_form.error.wrong_time' => 'The provided destroy time was provided in the wrong format. Please provide a full, positive number.',

		'view.upload.title' => 'Upload completed',
		'view.upload.all_files' => 'All files from this upload',
		'view.upload.share_all_files' => 'Share all files',
		'view.upload.share_individual_files' => 'Share individual files',

		'view.show_upload.title' => 'Download files',
		'view.show_upload.error.not_found' => 'The requested upload was not found. Maybe you mistyped the URL, or the upload was deleted.',

		'view.show_file.title' => 'Download file',
		'view.show_file.error.not_found' => 'The requested file was not found. Maybe you mistyped the URL, or the file was deleted.',

		'view.download.button_download' => 'Download',

		'view.login.field_username' => 'Username',
		'view.login.field_password' => 'Password',

		'view.admin.title' => 'Administration',
		'view.admin.body' => 'View the upload history, share past uploads and delete files that are not needed anymore.',
		'view.admin.credentials_required' => 'This access is protected. Please enter your credentials.',
		'view.admin.error.wrong_credentials' => 'The credentials you entered are not correct.',

		'view.not_found.title' => 'Not found',
		'view.not_found.body' => 'The page or file you requested is not available.',

		'view.error.title' => 'Something went wrong',
		'view.error.body' => 'An error occurred.'
	],
	'DE' => [

	]
];
