<?php
require_once("google_api_info.php");

$redirect_uri = "http://localhost/D%C3%A9veloppement/HISTU/liste_groupes.php";

require_once ('Google/Client.php');
require_once ('Google/Http/Request.php');

require_once "load_mustache.php"
require_once "config.php"

$tpl = $mustache->loadTemplate("liste_groupes.html");

session_start();

$client = new Google_Client();
$client->setApplicationName("School Phenomenon");
$client->setScopes(array(
    'https://www.google.com/m8/feeds/'
));

// Documentation: http://code.google.com/googleapps/domain/provisioning_API_v2_developers_guide.html
// Visit https://code.google.com/apis/console to generate your
// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.

$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);

if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
}

if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    header('Location: ' . $redirect_uri);
}

if (isset($_SESSION['access_token'])) {

    $client->setAccessToken($_SESSION['access_token']);

    //Get Email of User ------------------------------------
    // You are now logged in
    // We need the users email address for later use. We can get that here.


    $add = new Google_Http_Request("https://www.google.com/m8/feeds/groups/default/full");
	$add = $client->getAuth()->sign($add);

    $submit       = $client->getIo()->executeRequest($add);
	if ($submit[2] !== "200") {
		die($tpl->render(array(
			"errors" => array("message" => $submit[2]." : Impossible de joindre google.")
		)));
	}

	libxml_use_internal_errors(true);
	$groups = simplexml_load_string($submit[0]);
	if ($groups === FALSE) {
		$groups = array(
			"errors" => libxml_get_errors()
		);
	}
	echo $tpl->render($groups);

    // The access token may have been updated lazily.
    $_SESSION['access_token'] = $client->getAccessToken();
} else {
    $authUrl = $client->createAuthUrl();
}

if (isset($authUrl)) {
    echo $tpl->render(array(
    	"authUrl" => $authUrl
    ));
}

?>
