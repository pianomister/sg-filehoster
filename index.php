<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/sg-filehoster.php';
use SGFilehoster as FH;

// set default action params
$action = FH\UI_HOMEPAGE;
$data = [
	'id' => null
];

// catch action short links
if ( isset($_GET[FH\PARAM_SHORT_UPLOAD]) ) {
	$action = FH\ACTION_SHOW_UPLOAD;
	$data['id'] = $_GET[FH\PARAM_SHORT_UPLOAD];
} elseif ( isset($_GET[FH\PARAM_SHORT_FILE]) ) {
	$action = FH\ACTION_SHOW_FILE;
	$data['id'] = $_GET[FH\PARAM_SHORT_FILE];
}
// catch action
if( isset($_GET[FH\PARAM_ACTION]) ) {
	$action = $_GET[FH\PARAM_ACTION];
}

// execute action
$viewData = FH\SGFilehoster::executeAction($action, $data);

// render page
$template = file_get_contents(__DIR__ . '/template.html');
$template = str_replace('{TITLE}', FH\UI_TITLE, $template);
$template = str_replace('{DESCRIPTION}', FH\Labels::get('view.welcome.title'), $template);
$template = str_replace('{THEME}', FH\SGFilehoster::getTheme(), $template);
$content = FH\SGFilehoster::getHeader();
$content .= FH\SGFilehoster::getView($viewData['action'], $viewData);
$content .= FH\SGFilehoster::getFooter();
echo str_replace('{CONTENT}', $content, $template);
?>