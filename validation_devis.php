<?php
require_once 'config/config.php';
require_once "fonctions/load_mustache.php";
require_once "fonctions/erreurs.php";


$tpl = getMustacheTemplate("validation_devis.html");
$errors = getErrorManager($tpl);

$fichier_articles = "config/articles.json";
$donnees_articles = json_decode(file_get_contents($fichier_articles));

// Les is sont générés dans l’ordre chronologique. Cette ne risque pas de créer
// des collisions tant que l’on ne s’approche pas d’une commande par seconde.
$id_commande = (time()-1395802258)*10 + rand(0,10);

function clean() {
	global $folder;
	foreach(scandir($folder) as $f) {
		$f="$folder/$f";
		if (!is_dir($f)) unlink($f);
	}
	rmdir($folder);
}

//////////////// Création du dossier de la commande ////
$folder = sprintf(COMMAND_FOLDER_FORMAT, $id_commande);
$dossier_cree = @mkdir($folder, 0777, true);
if ($dossier_cree !== TRUE) {
	trigger_error("Impossible de créer le dossier de la commande.");
}

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
$personnalisations = new Set();
foreach ($donnees_articles->personnalisations as $perso) {
	$nuancier->add($perso->perso);
}


function champ_necessaire() {
	foreach(func_get_args() as $champ) {
		if (!isset($_POST[$champ])) {
			trigger_error("Le champ « $champ » est nécessaire et n’a pas été fourni");
		}
	}
}

$devis = array();

$devis['id-commande'] = $id_commande;

/////////// Ajout de la date à laquelle la commande a été effectuée ////
date_default_timezone_set('Europe/Paris');
$devis['date-commande'] = (new DateTime())->format('d/m/Y');

//////////////// Validation de la date de naissance ///////////////////
champ_necessaire("date-naissance-annee", "date-naissance-mois", "date-naissance-jour");
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
champ_necessaire('date-livraison');

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
champ_necessaire($champ);
	$email = filter_var($_POST[$champ], FILTER_VALIDATE_EMAIL);
	if ($email === FALSE) {
		$errors->add('L’adresse email fournie est invalide', 'Email invalide');
	} else {
		$devis[$champ] = $_POST[$champ];
	}

////////////// Validation des champs de texte obligatoires //////////////////
$champs = array("prenom", "nom", 'association', 'telephone', 'adresse', 'ville');
foreach ($champs as $champ) {
	champ_necessaire($champ);
	$val = trim((string) $_POST[$champ]);
	if (empty($val)) {
		$errors->add("Vous n’avez pas renseigné votre $champ.", "Champ vide");
	} else {
		$devis[$champ] = $val;
	}
}

//////////////// Validation des champs numériques ///////
$champs = array('code-postal');
foreach ($champs as $champ) {
  champ_necessaire($champ);
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

////////////// Validation des articles ////////////////
$finfo = finfo_open(FILEINFO_MIME_TYPE);
foreach ($donnees_articles->liste_articles as $article_conf) {
	$article_id = $article_conf->id;
	if (isset($_POST[$article_id]) and !empty($_POST[$article_id])) {
		$article = $_POST[$article_id];
		$nbr = intval($article['nbr']);
		if ($nbr > 0) {
			$min = $article_conf->nbr_min;
			if ($nbr < $min) {
				$msg = sprintf('Vous n’avez commandé que %d articles du type « %s ». Le minimum est de %d',
												$nbr, $article_conf->nom_complet, $min);
				$errors->add($msg, 'Trop petit nombre d’articles');
				continue;
			}
			$article_devis = array( 'id_article' => $article_id,
															'nom_complet' => $article_conf->nom_complet,
															'nbr' => $nbr,
															'couleurs' => array(),
															'personnalisations' => array(),
															'images' => array());
			///// Validation des couleurs ///////
			foreach ($article_conf->choix_couleurs as $couleur) {
				$id_couleur = $couleur->type_couleur;
				$couleur = $article['couleurs'][$id_couleur];
				if ($nuancier->has($couleur)) {
					$article_devis['couleurs'][] = array('type'=>$id_couleur, 'couleur'=>(string)$couleur);
				}
			}
			///// Validation des personnalisations ///////
			if (isset($article['perso'])) {
				foreach ($article['perso'] as $perso => $val) {
					$article_devis['personnalisations'][] = $perso;
				}
			}
			//// Validation des images personnalisées ////
			$fileKey = "$article_id-files";
			if (!isset($_FILES[$fileKey])) $errors->add("Champ fichier non reçu pour $article_id.", FALSE,json_encode($_FILES), TRUE);
			$files = $_FILES[$fileKey];
			foreach ($files['error'] as $num_file => $error) {
				$num_file = intval($num_file); //Protection contre les déformations de $_FILES
				if ($error === UPLOAD_ERR_OK) {
					// Gérer l’upload de fichier en php en étant à la fois flexible sur
					// les formats autorisés et sérieux avec la sécurité est un enfer...
					$nom = $files['name'][$num_file];
					$tmp = $files['tmp_name'][$num_file];
					$type = explode('/', finfo_file($finfo, $tmp)); // Don’t mess with me, I check mimetypes
					if ($type[0] !== 'image') {
						$errors->add("$nom: type de fichier incorrect ($type[0]).");
					}
					$extension = strstr($nom,'.');
					if ($extension===FALSE or strpos($type[1], substr($extension,1)) !== 0 ) {
						// He tried to fool me, but I’m smarter ;)
						if ( $extension!=='.jpg' ) $extension = '.image';
					}
					$nouvfichier = $folder.'/image-'.$article_id.'-'.$num_file.$extension;
					$fait = move_uploaded_file($tmp, $nouvfichier);
					if (!$fait) {
						$errors->add("Impossible d’enregistrer le fichier $nom", 'Erreur lors du déplacement d’un fichier');
						break;
					}
					$x = filter_var($article['images']['x'][$num_file], FILTER_VALIDATE_FLOAT);
					$y = filter_var($article['images']['y'][$num_file], FILTER_VALIDATE_FLOAT);
					$w = filter_var($article['images']['w'][$num_file], FILTER_VALIDATE_FLOAT);
					$h = filter_var($article['images']['w'][$num_file], FILTER_VALIDATE_FLOAT);
					$article_devis['images'][] = array(
						"fichier" => $nouvfichier,
						"nom" => $nom,
						"x" => filter_var($article['images']['x'][$num_file], FILTER_VALIDATE_FLOAT), // Percentage. Can be false
						"y" => filter_var($article['images']['y'][$num_file], FILTER_VALIDATE_FLOAT),
						"w" => filter_var($article['images']['w'][$num_file], FILTER_VALIDATE_FLOAT),
						"h" => filter_var($article['images']['h'][$num_file], FILTER_VALIDATE_FLOAT),
					);
				} else if ($error === UPLOAD_ERR_NO_FILE) continue; //Champ d’upload laissé vide
				else {
					$msg = "Erreur dans les fichiers de $article_id. ";
					if ($error === UPLOAD_ERR_INI_SIZE) {
						$msg .= "Taille maximale des fichiers supportée par ce serveur: ";
						$msg .= ini_get('upload_max_filesize') . "o.";
					}
					$errors->add($msg, "Erreur de téléversement", "Code d’erreur d’upload: $error.");
				}
			}
			$devis['articles'][] = $article_devis;
		}
	}
}

////////// Ajout d’un fichier contenant les informations de la commande
$devisjson = json_encode($devis);
$res = file_put_contents($folder.'/commande.json', $devisjson);
if (intval($res) < strlen($devisjson)) {
	clean();
	$errors->add('Impossible de sauvegarder la commande.');
}

/////////////// Envoi du mail //////////////
$headers ='From: postmaster@school-phenomenon.net'."\n";
$headers .='Content-Type: text/plain; charset="utf-8"'."\n";
$headers .='Content-Transfer-Encoding: binary'; //Allows long lines
$emails = str_replace(' ', ', ', GOOGLE_EMAILS_ADMINS);
$adresse_visualisation = ADRESSE_SITE.'/visualisation_commande.php?id='.$id_commande;
$emailtxt = getMustacheTemplate('email.txt')->render(array(
	'adresse_site' => ADRESSE_SITE,
	'id' => $id_commande,
	'devis' => $devis
));
$res = mail ( $emails , 'Nouvelle commande School Phenomenon' , $emailtxt, $headers);
if ($res === FALSE) {
	$errors->add('Impossible d’envoyer un mail aux administrateurs', 'Échec de l’envoi');
}

/////////// Arrêt si il y a des erreurs /////////////////////
if (count($errors) !== 0) {
	clean();
	trigger_error("Il y a des erreurs dans votre commande. Impossible de poursuivre.");
}

echo $tpl->render(array(
			"id"=> $id_commande,
			"dbg"=> FALSE
));
?>
