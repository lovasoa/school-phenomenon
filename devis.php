<?php
require_once "config.php";
require_once "load_mustache.php";

$tpl = $mustache->loadTemplate("devis.html");

$articles = json_decode(file_get_contents("articles.json"));

echo $tpl->render(array(
	"id_commande" => rand(),
	"articles" => $articles
));

?>
