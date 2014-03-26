<?php
require_once "load_mustache.php";
require_once "../config/config.php";

function create_gcontact_file ($data, $id) {
	global $mustache;
	$data["gcontact-group-id"] = GROUPE_CONTACTS;
	$tpl = $mustache->loadTemplate("google-contact.xml");
	$gdata = $tpl->render($data);
	$filename = sprintf(CONTACT_FILENAME_FORMAT, $id);
	$ret = file_put_contents($filename, $gdata);
	if ($ret !== strlen($gdata)) {
		trigger_error("Unable to write gcontact file.", E_USER_ERROR);
	}
	return $filename;
}

?>
