<?php
/* Adresse gmail de l'administrateur. Seule une personne connectée avec un 
compte google correspondant à l'une de ces adresses pourra importer les 
*/
const GOOGLE_EMAILS_ADMINS = "contact@school-phenomenon.com pere.jobs@gmail.com";

/* Définit l'id du groupe dans lequel seront enregistrés les contacts
Vous pouvez trouver quoi mettre ici à partir du script liste_groupes.php
*/
const GROUPE_CONTACTS = "http://www.google.com/m8/feeds/groups/pere.jobs%40gmail.com/base/67a32a548cc77268";


/* Définit l'emplacement où sont enregistrés les fichiers de contact en attendant
  d’être ajoutés au carnet d'adresse de l'administrateur.
  Le %d sera remplacé par un numéro propre à chaque contact.
*/
const COMMAND_FOLDER_FORMAT = "commandes/commande-%d";

/* Définit l'emplacement où sont enregistrés les fichiers de contact en attendant
  d’être ajoutés au carnet d'adresse de l'administrateur.
  Le %d sera remplacé par un numéro propre à chaque contact.
*/
const CONTACT_FILENAME_FORMAT = "commandes/commande-%d/gcontact.xml";

?>
