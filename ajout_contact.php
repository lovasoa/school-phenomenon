 <?php
require_once("google_api_info.php");

$redirect_uri = "http://localhost/D%C3%A9veloppement/HISTU/ajout_contact.php";

require_once ('Google/Client.php');
require_once ('Google/Http/Request.php');

require_once "load_mustache.php";
require_once "config/config.php";

$tpl = $mustache->loadTemplate("ajout_contact.html");

session_start();

$client = new Google_Client();
$client->setApplicationName("School Phenomenon");
$client->setScopes(array(
	'https://www.googleapis.com/auth/userinfo.email',
	'https://www.google.com/m8/feeds/',
));

// Documentation: http://code.google.com/googleapps/domain/provisioning_API_v2_developers_guide.html
// Visit https://code.google.com/apis/console to generate your
// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.

$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);

function fatal_error($msg, $title=FALSE) {
	global $tpl;
	die($tpl->render(array(
		"errors" => array(
			"title" => $title,
			"message" => $msg
			)
	)));
}

if (isset($_GET["contact_id"])) {
	$_SESSION['contact_id'] = intval($_GET['contact_id']);
}

if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
}

if (isset($_GET['code'])) {
	try {
		$client->authenticate($_GET['code']);
		$_SESSION['access_token'] = $client->getAccessToken();
		header("Location: ".$redirect_uri);
		die();
    } catch (Exception $e) {
		fatal_error($e, "Authentification impossible");
	}
}

if (isset($_SESSION['access_token'])) {

    $client->setAccessToken($_SESSION['access_token']);

	// On est maintenant identifié

    //------------------------------------
    // Vérifie que l'utilisateur est autorisé
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


	// -----------------
	// Ajout du contact
	if (! isset($_SESSION['contact_id'])) {
		fatal_error("Aucun contact à ajouter n’a été fourni.", "Aucun contact à ajouter");
	}
	$contact_id = intval($_SESSION['contact_id']);
	unset($_SESSION['contact_id']);

    $contact_filename = sprintf(CONTACT_FILENAME_FORMAT, $contact_id);

	if ( !file_exists($contact_filename) ) {
		fatal_error("Le contact à ajouter n'est plus présent sur le serveur. Cette erreur se produit notemment lorsque vous tentez d’ajouter le même contact pour la deuxième fois.",
					"Contact introuvable");
	}

	$contact = @file_get_contents($contact_filename);
    $add = new Google_Http_Request("https://www.google.com/m8/feeds/contacts/default/full/");
    $add->setRequestMethod("POST");
    $add->setPostBody($contact);
    $add->setRequestHeaders(array(
        'content-length' => strlen($contact),
        'GData-Version' => '3.0',
        'content-type' => 'application/atom+xml; charset=UTF-8; type=feed'
    ));
	try {
		// Sign the request with the token
		$add = $client->getAuth()->sign($add);
	} catch (Exception $e) {
		fatal_error($e.". La solution est sans doute de vous déconnecter et de recommencer",
			"Erreur d’identification Google");
	}

    list($resp, $head, $code) = $client->getIo()->executeRequest($add);

	if ($code !== "201") {
		$msg = "Réponse négative de Google ($code) : " . html_entity_decode($resp);
		fatal_error($msg);
	}

	// C'est bon, le contact a été ajouté
	$contact = simplexml_load_string($resp);
	// Affiche la page de résultat
	echo $tpl->render(array(
		"success" => TRUE,
		"nom" => $contact->title
	));
	unlink($contact_filename);

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
