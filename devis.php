<?php
require_once "config/config.php";
require_once "load_mustache.php";

$tpl = $mustache->loadTemplate("devis.html");

$erreurs = array();

$fichier_articles = "config/articles.json";

$articles = json_decode(file_get_contents($fichier_articles));

if ($articles === NULL) {
	$erreurs[] = array(
		"title" => "Fichier de configuration invalide",
		"message" => "Impossible de lire le fichier de configuration ($fichier_articles) car il contient des erreurs.",
		"error_data" => 'Error '.json_last_error().' : '.json_last_error_msg()
	);
}

//Ajout des identifiants de personnalisation
$persos = $articles->personnalisations;
for($i=0; $i<count($persos); $i++) {
	$perso = $persos[$i];
	$perso->perso_id = str_replace(' ', '_', $perso->perso);
}

echo $tpl->render(array(
	"id_commande" => rand(),
	"articles" => $articles,
	"errors" => $erreurs,
	"html_id" => function($unescaped_id, Mustache_LambdaHelper $helper) {
		$invalide = "/^[^a-zA-Z]|[^a-zA-Z0-9.:\\-]/";
		return preg_replace($invalide, "_",  $helper->render($unescaped_id));
  	}
));

?>
