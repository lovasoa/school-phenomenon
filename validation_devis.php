<?php
require_once 'config/config.php';
require_once "create_gcontact_file.php";
require_once "load_mustache.php";
$tpl = $mustache->loadTemplate("validation_devis.html");


$fichier_articles = "config/articles.json";
$donnees_articles = json_decode(file_get_contents($fichier_articles));


/////////////////// Gestion des erreurs dans la commande ///////
class Errors extends ArrayObject {
	public function add ($message, $title=FALSE, $error_data=FALSE) {
		$this[] = array("message"=>$message, "title"=>$title, "arror_data"=>$error_data);
	}
}
$errors = new Errors;

function handle_error ($errno, $errmsg) {
  global $errors, $tpl;
	$errors->add ($errmsg, "Erreur ".$errno);
	die($tpl->render(array("errors"=>$errors)));
}
set_error_handler("handle_error");


/////////////// Gestion du nuancier /////////////////
class Set extends ArrayObject { // Class d'ensemble simpliste
	private $elms = array();
	public function add($elm) {$this->elms[$elm] = TRUE;}
	public function has($elm) {return isset($this->elms[$elm]);}
}
$nuancier = new Set();
foreach ($donnees_articles->nuancier as $nuance) {
	$nuancier->add($nuance->num);
}
var_dump($nuancier->has('noir'));
var_dump($nuancier->has('12'));

$devis = array();

//////////////// Validation de la date de naissance ///////////////////:
date_default_timezone_set("Europe/Paris");
$date_naissance = new DateTime();
$date_naissance->setDate($_POST["date-naissance-annee"],
												 $_POST["date-naissance-mois"],
												 $_POST["date-naissance-jour"]);
if ($date_naissance->diff(new DateTime())->y < 18) {
	//Moins de 18 ans
	$errors->add($date_naissance->diff(new DateTime())->y . " ans", "Trop jeune");
} else {
	$devis["date-naissance"] = $date_naissance->format('d/m/Y');
}

//////////////////// Validation de la date de livraison ////////////////////////
$date_livraison = DateTime::createFromFormat('d/m/Y', $_POST['date-livraison']);
if ($date_livraison === FALSE) {
	$errors->add("Format de la date de livraison invalide: ".$_POST['date-livraison']);
} else if ($date_livraison < new DateTime()) {
	$errors->add("Date de livraison dans le passé", "Date de livraison invalide");
} else {
	$devis['date-livraison'] = $date_livraison->format('d/m/Y');
}

////////////// Validation de l’email //////////////////
$champ = 'email';
if (!isset($_POST[$champ]) or empty($_POST[$champ])) {
	$errors->add('Vous devez fournir une adresse mail', 'Email');
} else {
	$email = filter_var($_POST[$champ], FILTER_VALIDATE_EMAIL);
	if ($email === FALSE) {
		$errors->add('L’adresse email fournie est invalide', 'Email invalide');
	} else {
		$devis[$champ] = $_POST[$champ];
	}
}

////////////// Validation des champs de texte obligatoires //////////////////
$champs = array("prenom", "nom", 'association', 'telephone', 'adresse', 'ville');
foreach ($champs as $champ) {
	if (!isset($_POST[$champ]) or empty($_POST[$champ])) {
		$errors->add("Vous n’avez pas renseigné votre $champ.", "Champ vide");
	} else {
		$devis[$champ] = $_POST[$champ];
	}
}

//////////////// Validation des champs numériques ///////
$champs = array('code-postal');
foreach ($champs as $champ) {
	if (isset($_POST[$champ])) {
		$val = filter_var($_POST[$champ], FILTER_VALIDATE_INT);
		if ($val === FALSE) {
			$errors->add("Valeur numérique incorrecte pour le champ $champ.", 'Valeur incorrecte');
		} else {
			$devis[$champ] = $val;
		}
	} else {
		$errors->add("Vous n’avez pas renseigné votre $champ.", 'Champ vide');
	}
}

//////////////// Infos supplémentaires //////////////////
$champ = 'infos-supplementaires';
if (isset($_POST[$champ]) and is_string($_POST[$champ]) and strlen(trim($_POST[$champ])) > 0) {
	$devis[$champ] = trim($_POST[$champ]);
}

if (count($errors) !== 0) {
	trigger_error("Il y a des erreurs dans votre commande. Impossible de poursuivre.");
}

////////////// Validation des articles ////////////////
foreach ($donnees_articles->liste_articles as $article_conf) {
	$article_id = $article_conf->id;
	if (isset($_POST[$article_id]) and !empty($_POST[$article_id])) {
		$article = $_POST[$article_id];
		$nbr = intval($article['nbr']);
		if ($nbr > 0) {
			$article_devis = array('nbr'=>$nbr);
			///// Validation des couleurs ///////
			foreach ($article_conf->couleur as $couleur) {
				if ($nuancier->has($article[$couleur['num']])) {
					$article_devis['couleurs'][] = $couleur;
				}
			}
			$devis['articles'][] = $article_devis;
		}
	}
}

$id_contact = rand();
create_gcontact_file($_POST, $id_contact);

echo $tpl->render(array(
	"errors" => $errors,
	"dbg" => json_encode(array(
							"devis" => $devis,
							"POST" => $_POST
						), JSON_PRETTY_PRINT)
));
?>
