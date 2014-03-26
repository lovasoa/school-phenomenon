<?php
require_once "config/config.php";
require_once "fonctions/load_mustache.php";
require_once "fonctions/erreurs.php";

$tpl = getMustacheTemplate("devis.html");
$erreurs = getErrorManager($tpl);

$fichier_articles = "config/articles.json";

$articles = json_decode(file_get_contents($fichier_articles));

if ($articles === NULL) {
	$erreurs[] = array(
		"title" => "Fichier de configuration invalide",
		"message" => "Impossible de lire le fichier de configuration ($fichier_articles) car il contient des erreurs.",
		"error_data" => 'Error '.json_last_error().' : '.json_last_error_msg()
	);
}

echo $tpl->render(array(
	"id_commande" => rand(),
	"articles" => $articles,
	"errors" => $erreurs,
	"html_id" => function($unescaped_id, Mustache_LambdaHelper $helper) {
		return str_replace(' ', "_",  $helper->render($unescaped_id));
	}
));

?>
