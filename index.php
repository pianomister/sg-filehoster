<?php
declare(strict_types=1);
// TODO debug
error_reporting(-1);

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
$content = FH\SGFilehoster::getHeader();
$content .= FH\SGFilehoster::getView($viewData['action'], $viewData);
$content .= FH\SGFilehoster::getFooter();
echo str_replace('{CONTENT}', $content, $template);

// ADMIN
/*
if ($_GET['pw'] == 'I8it') {
	echo '<h2>upps</h2>';
	$modulDir = opendir($moduldata_path);
	$fileCounter = 0;//zähler für Ausgabe von Fehlermeldung
	while ($file = readdir($modulDir))
	{
		if (is_file($moduldata_path.$file))
			$fileList[] = $file;
	}
	natcasesort($fileList);
	foreach ($fileList as $file)
		echo '<a href="'.$moduldata_path.$file.'">'.$file.'</a><br />';
}
<!--
<script type="text/javascript">
var isIe = (navigator.userAgent.toLowerCase().indexOf("msie") != -1
           || navigator.userAgent.toLowerCase().indexOf("trident") != -1);

var elem = document.getElementById('link');

window.addEventListener('load', function (e) {
	document.addEventListener('copy', function(e) {
		var textToPutOnClipboard = elem.value;
		console.log(textToPutOnClipboard);
		if (isIe) {
			window.clipboardData.setData('Text', textToPutOnClipboard);
		} else {
			e.clipboardData.setData('text/plain', textToPutOnClipboard);
		}
		e.preventDefault();
	});
	if(elem != null) {
		elem.focus();
		elem.select();
	}
});
</script>
-->
*/
?>