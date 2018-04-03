<?

function mydebug(&$string, $die = false)
{
	file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/debug.txt', mydump($string));
	if ($die)
		die();
}