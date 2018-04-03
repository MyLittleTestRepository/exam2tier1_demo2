<?
///const
define('CONTENT_ED_GID', 5);

////////include
$arInc = ['event_handler'];


foreach ($arInc as $file)
{
	$path_inc = dirname(__FILE__) . '/inc/' . $file . '.php';
	if (file_exists($path_inc))
		include_once($path_inc);
}
unset($path_inc);
unset($arInc);

function mydebug(&$string, $die = false)
{
	file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/debug.txt', mydump($string));
	if ($die)
		die();
}