 <?php
require_once "google_api_info.php";

$redirect_uri = "http://localhost/D%C3%A9veloppement/HISTU/ajout_contact.php";

require_once ('Google/Client.php');
require_once ('Google/Http/Request.php');

require_once "fonctions/load_mustache.php";
require_once "config/config.php";
require_once 'fonctions/create_gcontact.php';
require_once 'fonctions/erreurs.php';

$tpl = getMustacheTemplate("ajout_contact.html");
$errors = getErrorManager($tpl);

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
	$inputvarname = 'contact_id';
	if (!isset($_SESSION[$inputvarname]) or
			($contact_id = filter_var($_SESSION[$inputvarname], FILTER_VALIDATE_INT)) == 0) {
		$errors->add("La variable définissant le contact à ajouter n’a pas une valeur correcte:",
								"Aucun contact à ajouter",
								$inputvarname,
								TRUE);
	}
	unset($_SESSION[$inputvarname]);

		$contact = create_gcontact($contact_id);
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
		$errors->add($e.". La solution est sans doute de vous déconnecter et de recommencer",
			"Erreur d’identification Google", FALSE, FALSE);
	}

    list($resp, $head, $code) = $client->getIo()->executeRequest($add);

	if ($code !== "201") {
		$errors->add("Réponse négative de Google ($code) : " . html_entity_decode($resp),
									"Erreur Google n°$code",
									$contact,
									TRUE);
	}

	// C'est bon, le contact a été ajouté
	$contact = simplexml_load_string($resp);
	// Affiche la page de résultat
	echo $tpl->render(array(
		"success" => TRUE,
		"nom" => $contact->title
	));

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
