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
		'view.welcome.body' => 'Upload files, protect access, share the download link.',

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
		'view.general.login' => 'Login',
		'view.general.button_login' => 'Anmelden',
		'view.general.password_required' => 'Dieser Zugang ist geschützt. Bitte gib das Passwort ein.',
		'view.general.error.wrong_password' => 'Das eingegebene Passwort ist nicht korrekt.',
		'view.general.copy_action' => 'Link kopieren',

		'view.welcome.title' => 'Einfach Dateien teilen.',
		'view.welcome.body' => 'Eine oder mehrere Dateien hochladen, Zugriff schützen, Download-Link teilen.',

		'view.upload_form.title' => 'Upload',
		'view.upload_form.size_limit' => 'Dateigrößenlimit:',
		'view.upload_form.field_files' => 'Dateien auswählen ...',
		'view.upload_form.files_selected' => '{count} Dateien ausgewählt',
		'view.upload_form.field_option_password' => 'Zugriff zu hochgeladenen Dateien schützen',
		'view.upload_form.field_upload_password' => 'mit Passwort',
		'view.upload_form.field_option_time' => 'Dateien sind für begrenzte Zeit verfügbar',
		'view.upload_form.field_upload_time' => 'und werden gelöscht nach',
		'view.upload_form.value_upload_time_minutes' => 'Minute(n)',
		'view.upload_form.value_upload_time_hours' => 'Stunde(n)',
		'view.upload_form.value_upload_time_days' => 'Tag(en)',
		'view.upload_form.value_upload_time_weeks' => 'Woche(n)',
		'view.upload_form.value_upload_time_months' => 'Monat(en)',
		'view.upload_form.field_password' => 'Upload-Passwort',
		'view.upload_form.button_submit' => 'Upload',
		'view.upload_form.error.no_file' => 'Bitte wähle mindestens eine Datei für den Upload.',
		'view.upload_form.error.file_too_large' => 'Die Datei \'{file}\' ist zu groß ({size}).',
		'view.upload_form.error.empty_password' => 'Bitte gib ein Passwort ein, wenn du den Zugang zum Upload schützen willst.',
		'view.upload_form.error.empty_time' => 'Die Option für begrenzte Verfügbarkeit wurde ausgewählt, aber keine Ablaufzeit eingegeben.',
		'view.upload_form.error.wrong_time' => 'Die Ablaufzeit wurde im falschen Format eingegeben. Bitte gib eine ganze, positive Zahl ein.',

		'view.upload.title' => 'Upload abgeschlossen',
		'view.upload.all_files' => 'Alle Dateien',
		'view.upload.share_all_files' => 'Alle Dateien teilen',
		'view.upload.share_individual_files' => 'Einzelne Dateien teilen',

		'view.show_upload.title' => 'Dateien herunterladen',
		'view.show_upload.error.not_found' => 'Der aufgerufene Upload wurde nicht gefunden. Vielleicht hast du dich vertippt, oder der Upload wurde gelöscht.',

		'view.show_file.title' => 'Datei herunterladen',
		'view.show_file.error.not_found' => 'Die aufgerufene Datei wurde nicht gefunden. Vielleicht hast du dich vertippt, oder der Upload wurde gelöscht.',

		'view.download.button_download' => 'Download',

		'view.login.field_username' => 'Nutzername',
		'view.login.field_password' => 'Passwort',

		'view.admin.title' => 'Administration',
		'view.admin.body' => 'Zeige alle Uploads, teile vergangene Uploads und lösche Dateien, die nicht mehr benötigt werden.',
		'view.admin.credentials_required' => 'Dieser Zugang ist geschützt. Bitte gib deine Zugangsdaten ein.',
		'view.admin.error.wrong_credentials' => 'Die eingegebenen Zugangsdaten sind nicht korrekt.',

		'view.not_found.title' => 'Nicht gefunden',
		'view.not_found.body' => 'Die Seite oder Datei, die du aufgerufen hast, ist nicht verfügbar.',

		'view.error.title' => 'Etwas ist schiefgelaufen',
		'view.error.body' => 'Ein Fehler ist aufgetreten.'
	]
];
