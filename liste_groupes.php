<?php
require_once("google_api_info.php");

$redirect_uri = "http://localhost/D%C3%A9veloppement/HISTU/liste_groupes.php";

require_once ('Google/Client.php');
require_once ('Google/Http/Request.php');

require_once "load_mustache.php";
require_once "config/config.php";

$tpl = $mustache->loadTemplate("liste_groupes.html");

session_start();

function fatal_error($msg, $title=FALSE) {
	global $tpl;
	die($tpl->render(array(
		"errors" => array(
			"title" => $title,
			"message" => $msg
			)
	)));
}


$client = new Google_Client();
$client->setApplicationName("School Phenomenon");
$client->setScopes(array(
	'https://www.googleapis.com/auth/userinfo.email',
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
	try {
		$token_data = $client->verifyIdToken()->getAttributes();
		$user_email = $token_data["payload"]["email"];
	} catch (Exception $e) {
		fatal_error( $e,
					"Impossible d’obtenir les informations du token");
	}

	if ( !in_array($user_email, explode(' ', GOOGLE_EMAILS_ADMINS)) ) {
		fatal_error("Le compte avec lequel vous êtes identifié ne permet pas d'effectuer cette action. Vous pouvez ajouter des comptes autorisés à partir du fichier config/config.php",
				"Autorisation refusée");
	}

    $add = new Google_Http_Request("https://www.google.com/m8/feeds/groups/default/full");
	$add = $client->getAuth()->sign($add);

    list($resp, $headers, $code) = $client->getIo()->executeRequest($add);
	if ($code !== "200") {
		fatal_error("Google n’a pas donné la répose attendue : ".$resp,
					"Erreur ".$code);
	}

	libxml_use_internal_errors(true);
	$groups = simplexml_load_string($resp);
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
